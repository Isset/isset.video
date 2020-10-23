import moment from 'moment';

export function secondsToHours(seconds) {
    return moment.utc(seconds * 1000).format('HH:mm:ss');
}