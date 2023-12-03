<?php
namespace App\Service;

enum FlashMessageType: string {
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';
}