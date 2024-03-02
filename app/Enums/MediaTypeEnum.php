<?php
namespace App\Enums;
enum MediaTypeEnum: int {
    use BaseEnum;

    // Image Types
    case IMAGE = 10;
    case IMAGE_JPEG = 20;
    case IMAGE_PNG = 40;
    case IMAGE_GIF = 60;
    case IMAGE_WEBP = 80;

    // Audio Types
    case AUDIO = 100;
    case AUDIO_MP3 = 110;
    case AUDIO_WAV = 120;
    case AUDIO_OGG = 140;

    // Video Types
    case VIDEO = 150;
    case VIDEO_MP4 = 160;
    case VIDEO_AVG = 180;
    case VIDEO_WEBM = 190;

    // Document Types
    case DOCUMENT = 200;
    case DOCUMENT_PDF = 220;

    // Office Document Types
    case DOCUMENT_DOC = 240;
    case DOCUMENT_DOCX = 260;
    case DOCUMENT_XLS = 280;
    case DOCUMENT_XLSX = 300;
    case DOCUMENT_PPT = 320;
    case DOCUMENT_PPTX = 340;

    // Add more file types as needed

    case UNKNOWN = 99999999;
}
