import AVFoundation
import CoreGraphics
import CoreMedia
import Foundation
import ImageIO

func usage() -> Never {
    fatalError("usage: jpeg-sequence-to-mp4.swift <frames-dir> <frame-count> <fps> <output-mp4>")
}

guard CommandLine.arguments.count == 5 else {
    usage()
}

let framesDir = URL(fileURLWithPath: CommandLine.arguments[1], isDirectory: true)
let frameCount = Int(CommandLine.arguments[2]) ?? 0
let fps = Int32(CommandLine.arguments[3]) ?? 24
let outputURL = URL(fileURLWithPath: CommandLine.arguments[4])

guard frameCount > 0, fps > 0 else {
    usage()
}

try? FileManager.default.removeItem(at: outputURL)

let width = 1920
let height = 1080
let writer = try AVAssetWriter(outputURL: outputURL, fileType: .mp4)
let settings: [String: Any] = [
    AVVideoCodecKey: AVVideoCodecType.h264,
    AVVideoWidthKey: width,
    AVVideoHeightKey: height,
    AVVideoCompressionPropertiesKey: [
        AVVideoAverageBitRateKey: 12_000_000,
        AVVideoProfileLevelKey: AVVideoProfileLevelH264HighAutoLevel
    ]
]

let input = AVAssetWriterInput(mediaType: .video, outputSettings: settings)
input.expectsMediaDataInRealTime = false
let adaptor = AVAssetWriterInputPixelBufferAdaptor(
    assetWriterInput: input,
    sourcePixelBufferAttributes: [
        kCVPixelBufferPixelFormatTypeKey as String: kCVPixelFormatType_32BGRA,
        kCVPixelBufferWidthKey as String: width,
        kCVPixelBufferHeightKey as String: height
    ]
)

guard writer.canAdd(input) else {
    fatalError("Cannot add video input")
}
writer.add(input)
writer.startWriting()
writer.startSession(atSourceTime: .zero)

let queue = DispatchQueue(label: "colt.origin.video.writer")
let group = DispatchGroup()
group.enter()

input.requestMediaDataWhenReady(on: queue) {
    var frame = 0
    while input.isReadyForMoreMediaData && frame < frameCount {
        autoreleasepool {
            let name = String(format: "frame_%04d.jpg", frame)
            let url = framesDir.appendingPathComponent(name)
            guard
                let data = try? Data(contentsOf: url),
                let source = CGImageSourceCreateWithData(data as CFData, nil),
                let image = CGImageSourceCreateImageAtIndex(source, 0, nil)
            else {
                fatalError("Could not read frame \(name)")
            }

            var optionalBuffer: CVPixelBuffer?
            CVPixelBufferCreate(
                nil,
                width,
                height,
                kCVPixelFormatType_32BGRA,
                [
                    kCVPixelBufferCGImageCompatibilityKey as String: true,
                    kCVPixelBufferCGBitmapContextCompatibilityKey as String: true
                ] as CFDictionary,
                &optionalBuffer
            )
            guard let buffer = optionalBuffer else {
                fatalError("Could not create pixel buffer")
            }

            CVPixelBufferLockBaseAddress(buffer, [])
            let context = CGContext(
                data: CVPixelBufferGetBaseAddress(buffer),
                width: width,
                height: height,
                bitsPerComponent: 8,
                bytesPerRow: CVPixelBufferGetBytesPerRow(buffer),
                space: CGColorSpaceCreateDeviceRGB(),
                bitmapInfo: CGImageAlphaInfo.premultipliedFirst.rawValue | CGBitmapInfo.byteOrder32Little.rawValue
            )
            context?.interpolationQuality = .high
            context?.draw(image, in: CGRect(x: 0, y: 0, width: width, height: height))
            CVPixelBufferUnlockBaseAddress(buffer, [])

            let time = CMTime(value: CMTimeValue(frame), timescale: fps)
            if !adaptor.append(buffer, withPresentationTime: time) {
                fatalError("Could not append frame \(frame): \(String(describing: writer.error))")
            }
        }
        frame += 1
    }

    if frame >= frameCount {
        input.markAsFinished()
        writer.finishWriting {
            if writer.status == .failed {
                fatalError("Writer failed: \(String(describing: writer.error))")
            }
            group.leave()
        }
    }
}

group.wait()
