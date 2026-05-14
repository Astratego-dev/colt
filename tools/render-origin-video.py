from __future__ import annotations

import math
import sys
from pathlib import Path

from PIL import Image, ImageDraw, ImageEnhance, ImageFilter


def ease_in_out(t: float) -> float:
    t = max(0.0, min(1.0, t))
    return t * t * (3.0 - 2.0 * t)


def lerp(a: float, b: float, t: float) -> float:
    return a + (b - a) * t


def cover(image: Image.Image, width: int, height: int, scale: float, pan_x: float, pan_y: float) -> Image.Image:
    source_ratio = image.width / image.height
    target_ratio = width / height
    if source_ratio > target_ratio:
        base_h = height
        base_w = int(base_h * source_ratio)
    else:
        base_w = width
        base_h = int(base_w / source_ratio)

    resized = image.resize((int(base_w * scale), int(base_h * scale)), Image.Resampling.LANCZOS)
    left = int((resized.width - width) * (0.5 + pan_x))
    top = int((resized.height - height) * (0.5 + pan_y))
    left = max(0, min(resized.width - width, left))
    top = max(0, min(resized.height - height, top))
    return resized.crop((left, top, left + width, top + height))


def alpha_crop(image: Image.Image, padding: int = 16, threshold: int = 12) -> Image.Image:
    hard_alpha = image.getchannel("A").point(lambda value: 255 if value > threshold else 0)
    bbox = hard_alpha.getbbox()
    if not bbox:
        return image
    left = max(0, bbox[0] - padding)
    top = max(0, bbox[1] - padding)
    right = min(image.width, bbox[2] + padding)
    bottom = min(image.height, bbox[3] + padding)
    cropped = image.crop((left, top, right, bottom))
    clean_alpha = cropped.getchannel("A").point(lambda value: 0 if value <= threshold else value)
    cropped.putalpha(clean_alpha)
    return cropped


def camera_crop(world: Image.Image, center_x: float, center_y: float, view_width: float, out_w: int, out_h: int) -> Image.Image:
    view_height = view_width * out_h / out_w
    left = int(center_x - view_width / 2)
    top = int(center_y - view_height / 2)
    left = max(0, min(world.width - int(view_width), left))
    top = max(0, min(world.height - int(view_height), top))
    crop = world.crop((left, top, left + int(view_width), top + int(view_height)))
    return crop.resize((out_w, out_h), Image.Resampling.LANCZOS)


def character_pair(frames: list[Image.Image], progress: float) -> tuple[Image.Image, Image.Image, float]:
    # Keep the turn cinematic instead of constantly morphing the character.
    # The larger pose changes happen behind camera motion and title beats.
    beats = [
        (0.0, 0.31, 0, 0),
        (0.31, 0.43, 0, 2),
        (0.43, 0.64, 2, 2),
        (0.64, 0.78, 2, 3),
        (0.78, 0.9, 3, 4),
        (0.9, 1.0, 4, 5),
    ]
    for start, end, first, second in beats:
        if start <= progress <= end:
            local = 0.0 if start == end else (progress - start) / (end - start)
            return frames[first], frames[second], ease_in_out(local)
    return frames[-2], frames[-1], 1.0


def paste_character(world: Image.Image, first: Image.Image, second: Image.Image, mix: float, progress: float) -> tuple[float, float]:
    first = alpha_crop(first)
    second = alpha_crop(second)
    character_height = int(lerp(355, 430, ease_in_out(progress)))
    first_ratio = first.width / first.height
    second_ratio = second.width / second.height
    first_scaled = first.resize((int(character_height * first_ratio), character_height), Image.Resampling.LANCZOS)
    second_scaled = second.resize((int(character_height * second_ratio), character_height), Image.Resampling.LANCZOS)

    width = max(first_scaled.width, second_scaled.width)
    height = max(first_scaled.height, second_scaled.height)
    first_plate = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    second_plate = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    first_plate.alpha_composite(first_scaled, ((width - first_scaled.width) // 2, height - first_scaled.height))
    second_plate.alpha_composite(second_scaled, ((width - second_scaled.width) // 2, height - second_scaled.height))
    blended = Image.blend(first_plate, second_plate, mix)
    alpha = blended.getchannel("A")
    blended_rgb = blended.convert("RGB")
    blended_rgb = ImageEnhance.Contrast(blended_rgb).enhance(1.04 + progress * 0.05)
    blended_rgb = ImageEnhance.Sharpness(blended_rgb).enhance(1.08)
    blended = blended_rgb.convert("RGBA")
    blended.putalpha(alpha)

    world_x = int(world.width * 0.5)
    bottom = int(world.height * 0.724)
    x = int(world_x - width * 0.5)
    y = bottom - height
    face_center_y = y + int(height * 0.19)

    shadow_w = max(40, int(width * 0.62))
    shadow_h = 20
    shadow = Image.new("RGBA", (shadow_w, shadow_h), (0, 0, 0, 0))
    shadow_alpha = Image.new("L", (shadow_w, shadow_h), 0)
    ImageDraw.Draw(shadow_alpha).ellipse((0, 0, shadow_w, shadow_h), fill=130)
    shadow_alpha = shadow_alpha.filter(ImageFilter.GaussianBlur(18))
    shadow.putalpha(shadow_alpha)
    world.alpha_composite(shadow, (int(world_x - shadow_w * 0.5), bottom - 9))

    halo = Image.new("RGBA", blended.size, (206, 54, 154, 0))
    halo_alpha = alpha.filter(ImageFilter.GaussianBlur(15))
    halo.putalpha(ImageEnhance.Brightness(halo_alpha).enhance(0.18))
    world.alpha_composite(halo, (x - 2, y - 1))

    rim = Image.new("RGBA", blended.size, (247, 207, 132, 0))
    rim_alpha = alpha.filter(ImageFilter.GaussianBlur(2))
    rim_alpha = ImageEnhance.Brightness(rim_alpha).enhance(0.16)
    rim.putalpha(rim_alpha)
    world.alpha_composite(rim, (x + 3, y - 2))

    world.alpha_composite(blended, (x, y))
    return float(world_x), float(face_center_y)


def add_void(base: Image.Image, progress: float) -> None:
    if progress < 0.72:
        return

    t = ease_in_out((progress - 0.72) / 0.28)
    radius = int(lerp(36, 1480, t))
    cx = base.width // 2
    cy = int(lerp(405, 510, t))
    overlay = Image.new("RGBA", base.size, (0, 0, 0, 0))
    circle = Image.new("L", (radius * 2, radius * 2), 0)
    pixels = circle.load()
    for y in range(radius * 2):
        for x in range(radius * 2):
            dx = x - radius
            dy = y - radius
            distance = math.sqrt(dx * dx + dy * dy) / max(1, radius)
            if distance < 1:
                pixels[x, y] = int(255 * (1 - min(1, max(0, distance - 0.72) / 0.28) * 0.35))
    circle = circle.filter(ImageFilter.GaussianBlur(max(2, int(radius * 0.018))))
    black = Image.new("RGBA", circle.size, (0, 0, 0, int(lerp(90, 255, t))))
    black.putalpha(circle)
    overlay.alpha_composite(black, (cx - radius, cy - radius))
    base.alpha_composite(overlay)


def add_light_sweep(base: Image.Image, progress: float) -> None:
    overlay = Image.new("RGBA", base.size, (0, 0, 0, 0))
    draw = Image.new("RGBA", (base.width, 180), (0, 0, 0, 0))
    band = Image.new("RGBA", draw.size, (255, 78, 231, 0))
    alpha = Image.new("L", draw.size, 0)
    for y in range(draw.height):
        intensity = max(0.0, 1.0 - abs(y - 88) / 44)
        alpha_value = int(76 * intensity * (0.55 + 0.45 * math.sin(progress * math.pi)))
        if alpha_value > 0:
            alpha.paste(alpha_value, (0, y, draw.width, y + 1))
    band.putalpha(alpha.filter(ImageFilter.GaussianBlur(8)))
    angle = -8 + 4 * progress
    band = band.rotate(angle, expand=True, resample=Image.Resampling.BICUBIC)
    x = int(lerp(-260, 220, progress))
    y = int(lerp(104, 212, progress))
    overlay.alpha_composite(band, (x, y))
    base.alpha_composite(overlay)


def add_film_grade(base: Image.Image, progress: float) -> None:
    vignette = Image.new("RGBA", base.size, (0, 0, 0, 0))
    small_w, small_h = 480, 270
    mask = Image.new("L", (small_w, small_h), 0)
    pixels = mask.load()
    cx = small_w * 0.5
    cy = small_h * 0.52
    max_distance = math.sqrt(cx * cx + cy * cy)
    for y in range(small_h):
        for x in range(small_w):
            dx = (x - cx) / max_distance
            dy = (y - cy) / max_distance
            value = max(0.0, min(1.0, math.sqrt(dx * dx + dy * dy) * 1.72 - 0.36))
            alpha = int((82 + progress * 48) * value)
            pixels[x, y] = alpha
    mask = mask.resize(base.size, Image.Resampling.BICUBIC).filter(ImageFilter.GaussianBlur(18))
    vignette.putalpha(mask)
    base.alpha_composite(vignette)


def main() -> int:
    if len(sys.argv) not in (4, 5, 6):
        print("usage: render-origin-video.py <repo> <frames-out-dir> <frame-count> [jpg|webp] [quality]")
        return 2

    repo = Path(sys.argv[1])
    out_dir = Path(sys.argv[2])
    frame_count = int(sys.argv[3])
    image_format = (sys.argv[4] if len(sys.argv) >= 5 else "jpg").lower()
    quality = int(sys.argv[5]) if len(sys.argv) >= 6 else (76 if image_format == "webp" else 90)
    extension = "webp" if image_format == "webp" else "jpg"
    out_dir.mkdir(parents=True, exist_ok=True)

    width, height = 1920, 1080
    world_width, world_height = 3840, 2160
    background = Image.open(repo / "assets/scene-01-origin/colt-origin-world-clean.png").convert("RGB")
    frames = [
        Image.open(repo / f"assets/scene-01-origin/guardian-frame-{index:02d}.png").convert("RGBA")
        for index in range(1, 7)
    ]

    for frame in range(frame_count):
        progress = frame / max(1, frame_count - 1)
        camera = ease_in_out(progress)
        world = cover(background, world_width, world_height, 1.08, 0.0, 0.0).convert("RGBA")
        world = ImageEnhance.Color(world).enhance(1.08 + progress * 0.13)
        world = ImageEnhance.Contrast(world).enhance(1.04 + progress * 0.06)
        world = ImageEnhance.Sharpness(world).enhance(1.16)

        first, second, mix = character_pair(frames, progress)
        face_x, face_y = paste_character(world, first, second, mix, progress)

        view_width = lerp(world_width, 720, camera)
        center_x = lerp(world_width * 0.5, face_x, ease_in_out(max(0.0, progress - 0.12) / 0.88))
        center_y = lerp(world_height * 0.53, face_y + 64, ease_in_out(max(0.0, progress - 0.18) / 0.82))
        if progress > 0.76:
            head_t = ease_in_out((progress - 0.76) / 0.24)
            view_width = lerp(view_width, 470, head_t)
            center_y = lerp(center_y, face_y, head_t)

        plate = camera_crop(world, center_x, center_y, view_width, width, height)
        add_light_sweep(plate, progress)
        add_film_grade(plate, progress)
        add_void(plate, progress)

        if progress > 0.9:
            fade = Image.new("RGBA", plate.size, (0, 0, 0, int(220 * ease_in_out((progress - 0.9) / 0.1))))
            plate.alpha_composite(fade)

        rgb = plate.convert("RGB")
        save_path = out_dir / f"frame_{frame:04d}.{extension}"
        if image_format == "webp":
            rgb.save(save_path, "WEBP", quality=quality, method=6)
        else:
            rgb.save(save_path, "JPEG", quality=quality, optimize=True, progressive=False)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
