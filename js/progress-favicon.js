let canvas = document.querySelector('canvas');
let context = canvas.getContext('2d');
let favicon = document.querySelector('link[rel*="icon"]');

// Create favicon dynamically if it doesn't exist
if (!favicon) {
    let link = document.createElement('link');
    link.type = 'image/x-icon';
    link.rel = 'shortcut icon';
    document.getElementsByTagName('head')[0].appendChild(link);
    favicon = document.querySelector('link[rel*="icon"]');
}

export function updateFaviconProgress(progress) {
    let n = progress;
    context.clearRect(0, 0, 32, 32);

    context.lineWidth = 8;
    context.strokeStyle = '#f58025';
    
    context.beginPath();
    context.moveTo(0, 0);

    if (n <= 25) {
        context.lineTo((32 / 25) * n, 0);
    }
    if (n > 25) {
        context.lineTo(32, 0);
        context.moveTo(32, 0);
    }
    if (n > 25 && n <= 50) {
        context.lineTo(32, (32 / 25) * (n - 25));
    }
    if (n > 50) {
        context.lineTo(32, 32);
        context.moveTo(32, 32);
    }
    if (n > 50 && n <= 75) {
        context.lineTo(-((32 / 25) * (n - 75)), 32);
    }
    if (n > 75) {
        context.lineTo(0, 32);
        context.moveTo(0, 32);
    }
    if (n > 75 && n <= 100) {
        context.lineTo(0, -((32 / 25) * (n - 100)));
    }
    context.stroke();
    favicon.href = canvas.toDataURL('image/png');
}
