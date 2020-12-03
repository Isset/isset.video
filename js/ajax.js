export async function wpAjax (action, post = undefined) {
    const {nonce, ajaxUrl} = window.IssetVideoPublisherAjax;

    let form = new FormData();

    form.set('_ajax_nonce', nonce);
    form.set('action', action);

    if (post) {
        for (const key of Object.keys(post)) {
            form.set(key, post[key]);
        }
    }

    let archiveUrlPromise = await fetch(ajaxUrl, {
        method: 'POST',
        body: form
    });

    return new Promise((resolve, reject) => {
        archiveUrlPromise.json().then(json => {
            resolve(json);
        }).catch(err => {
            reject(err);
        })
    });
}

export async function archiveAjax(uri, queryParams = {}, method = 'GET', post = {}) {
    // noinspection JSUnresolvedVariable
    const {archiveUrl, archiveToken} = window.IssetVideoArchiveAjax;

    return ajax(`${archiveUrl}${uri}?${toQueryParameters(queryParams)}`, archiveToken, method, post);
}

export async function publisherAjax(uri, queryParams = {}, method = 'GET', post = {}) {
    // noinspection JSUnresolvedVariable
    const {publisherUrl, publisherToken} = window.IssetVideoArchiveAjax;

    return ajax(`${publisherUrl}${uri}?${toQueryParameters(queryParams)}`, publisherToken, method, post);
}

async function ajax(url, token, method = 'GET', post = {}) {
    const options = {
        method,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'x-token-auth': token,
        },
    };

    if (Object.keys(post).length > 0) {
        options.body = JSON.stringify(post);
    }

    let ajaxPromise = await fetch(url, options);

    return new Promise((resolve, reject) => {
        if (ajaxPromise.status === 204) {
            resolve({});
        }

        ajaxPromise.json().then(json => {
            resolve(json);
        }).catch(err => {
            reject(err);
        })
    });
}

function toQueryParameters(data) {
    let arraysString = '';
    const values = {};

    Object.keys(data).forEach((key) => {
        if (Array.isArray(data[key])) {
            data[key].forEach(itemValue => arraysString = `${arraysString}&${key}[]=${itemValue}`);
        } else {
            values[key] = data[key];
        }
    });

    const parameters = new URLSearchParams(values);

    return `${parameters.toString()}${arraysString}`;
}