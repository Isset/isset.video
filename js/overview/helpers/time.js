import moment from 'moment';

export function secondsToHours(seconds) {
    return moment.utc(seconds * 1000).format('HH:mm:ss');
}

export function dateTimeToHumanlyReadable(date) {
    return moment(date).format('YYYY-MM-DD HH:mm');
}