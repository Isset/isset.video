let canvas = null;
let favicon = null;

window.addEventListener('load', () => {
    let canvas = document.querySelector('canvas#favicon-canvas');
    let favicon = document.querySelector('link[rel*="icon"]');

    // Create favicon dynamically if it doesn't exist
    if (!favicon) {
        let link = document.createElement('link');
        link.type = 'image/x-icon';
        link.rel = 'shortcut icon';
        document.getElementsByTagName('head')[0].appendChild(link);
        favicon = document.querySelector('link[rel*="icon"]');
    }
});

export function updateFaviconProgress(progress) {
    let canvas = document.querySelector('canvas#favicon-canvas');
    let favicon = document.querySelector('link[rel*="icon"]');

    let context = canvas.getContext('2d');
    context.clearRect(0, 0, 32, 32);

    context.lineWidth = 8;
    context.strokeStyle = '#f58025';

    context.beginPath();
    context.moveTo(0, 0);
    drawRectangle(context, progress);
    context.stroke();
    favicon.href = canvas.toDataURL('image/png');
}

function drawRectangle(context, progress) {
    const pixelPerPercent = (32 / 25);
    let n = progress;

    context.lineTo(pixelPerPercent * Math.min(n, 25), 0);

    if (n <= 25) {
        return;
    }

    context.lineTo(32, pixelPerPercent * Math.min(n - 25, 25));

    if (n <= 50) {
        return;
    }

    context.lineTo(32 - (pixelPerPercent * Math.min(n - 50, 25)), 32);

    if (n <= 75) {
        return;
    }

    context.lineTo(0, 32 - (pixelPerPercent * Math.min(n - 75, 25)));
}
