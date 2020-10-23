export default function filesize(bytes) {
    return humanReadableBytes(bytes, ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']);
}


export function bitrate(bits) {
    return humanReadableBytes(bits, ['b/s', 'Kb/s', 'Mb/s', 'Gb/s']);
}


function humanReadableBytes(bytes, labels) {
    if (!bytes) {
        return '-';
    }

    let decimals = 2;

    if (bytes === 0) return '0 ' + labels[0];

    const k = 1000;
    const dm = decimals < 0 ? 0 : decimals;

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + labels[i];
}
