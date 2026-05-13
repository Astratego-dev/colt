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


def character_pair(frames: list[Image.Image], progress: float) -> tuple[Image.Image, Image.Image, float]:
    stops = [0.0, 0.24, 0.43, 0.63, 0.79, 1.0]
    for index in range(len(stops) - 1):
        if stops[index] <= progress <= stops[index + 1]:
            local = (progress - stops[index]) / (stops[index + 1] - stops[index])
            return frames[index], frames[index + 1], ease_in_out(local)
    return frames[-2], frames[-1], 1.0


def paste_character(base: Image.Image, first: Image.Image, second: Image.Image, mix: float, progress: float) -> None:
    zoom = ease_in_out(progress)
    scale = lerp(0.105, 1.42, zoom)
    if progress > 0.82:
        scale = lerp(scale, 2.22, ease_in_out((progress - 0.82) / 0.18))

    width = int(first.width * scale)
    height = int(first.height * scale)
    first_scaled = first.resize((width, height), Image.Resampling.LANCZOS)
    second_scaled = second.resize((width, height), Image.Resampling.LANCZOS)

    blended = Image.blend(first_scaled, second_scaled, mix)
    alpha = blended.getchannel("A")

    bottom = int(786 + 108 * ease_in_out(max(0.0, progress - 0.48) / 0.52))
    x = int(base.width * 0.5 - width * 0.5)
    y = bottom - height

    shadow_w = max(40, int(width * 0.62))
    shadow_h = max(8, int(26 * scale))
    shadow = Image.new("RGBA", (shadow_w, shadow_h), (0, 0, 0, 0))
    shadow_alpha = Image.new("L", (shadow_w, shadow_h), 0)
    ImageDraw.Draw(shadow_alpha).ellipse((0, 0, shadow_w, shadow_h), fill=170)
    shadow_alpha = shadow_alpha.filter(ImageFilter.GaussianBlur(max(6, int(18 * scale))))
    shadow.putalpha(shadow_alpha)
    base.alpha_composite(shadow, (int(base.width * 0.5 - shadow_w * 0.5), bottom - int(18 * scale)))

    glow = Image.new("RGBA", blended.size, (217, 183, 111, 0))
    glow_alpha = alpha.filter(ImageFilter.GaussianBlur(max(4, int(12 * scale))))
    glow.putalpha(ImageEnhance.Brightness(glow_alpha).enhance(0.28))
    base.alpha_composite(glow, (x, y))
    base.alpha_composite(blended, (x, y))


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


def main() -> int:
    if len(sys.argv) != 4:
        print("usage: render-origin-video.py <repo> <frames-out-dir> <frame-count>")
        return 2

    repo = Path(sys.argv[1])
    out_dir = Path(sys.argv[2])
    frame_count = int(sys.argv[3])
    out_dir.mkdir(parents=True, exist_ok=True)

    width, height = 1920, 1080
    background = Image.open(repo / "assets/scene-01-origin/colt-origin-world-clean.png").convert("RGB")
    frames = [
        Image.open(repo / f"assets/scene-01-origin/guardian-frame-{index:02d}.png").convert("RGBA")
        for index in range(1, 7)
    ]

    for frame in range(frame_count):
        progress = frame / max(1, frame_count - 1)
        zoom = 1.0 + 0.78 * ease_in_out(progress)
        pan_x = lerp(0.0, -0.07, ease_in_out(progress))
        pan_y = lerp(0.0, 0.08, ease_in_out(progress))
        plate = cover(background, width, height, zoom, pan_x, pan_y).convert("RGBA")
        plate = ImageEnhance.Color(plate).enhance(1.08 + progress * 0.16)
        plate = ImageEnhance.Contrast(plate).enhance(1.05 + progress * 0.08)

        first, second, mix = character_pair(frames, progress)
        paste_character(plate, first, second, mix, progress)
        add_light_sweep(plate, progress)
        add_void(plate, progress)

        if progress > 0.9:
            fade = Image.new("RGBA", plate.size, (0, 0, 0, int(220 * ease_in_out((progress - 0.9) / 0.1))))
            plate.alpha_composite(fade)

        rgb = plate.convert("RGB")
        rgb.save(out_dir / f"frame_{frame:04d}.jpg", quality=90, optimize=True, progressive=False)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
