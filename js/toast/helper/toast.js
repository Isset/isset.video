import React from 'react';
import ReactDOM from 'react-dom';
import Message from '../components/message';

export function showMessage(message, level = 'success', duration = 2000) {
    const div = document.createElement('div');
    ReactDOM.render(
        <Message container={div} message={message} duration={duration} level={level} />,
        document.body.appendChild(div)
    );
}