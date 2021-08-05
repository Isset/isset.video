import ReactDOM from 'react-dom';
import React from 'react';
import Editor from './editor';

class PlayerSettings extends React.Component {

    render() {
        return <Editor />;
    }

}

window.addEventListener('load', () => {
    if (typeof adminpage !== 'undefined' && adminpage === 'videos_page_isset-video-player-settings') {
        const {loggedIn} = window.IssetVideoPublisherAjax;

        if (loggedIn) {
            const playerSettingsContainer = document.getElementById('isset-video-player-settings');
            ReactDOM.render(<PlayerSettings />, playerSettingsContainer);
        }
    }
});