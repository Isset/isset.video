import React from 'react';
import ReactDOM from 'react-dom';
import {publisherAjax} from '../../ajax';
import {DEFAULT_PROVIDER, getExample} from '../helpers/advertisement';
import {__} from '@wordpress/i18n';
import {showMessage} from '../../toast/helper/toast';
import SettingsForm from './settingsForm';

class AdvertisementSettings extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            loaded: false,
            advertisement: {},
        };
    }

    componentDidMount() {
        publisherAjax('api/settings/advertisement').then(result => {
            if (result && !Array.isArray(result)) {
                const {enabled, skippable, provider, uri, positions} = result;
                this.setState({
                    advertisement: {
                        enabled,
                        skippable,
                        uri,
                        positions,
                        provider: provider ? provider.id : DEFAULT_PROVIDER,
                    }, loaded: true}
                );
            } else {
                this.setState({loaded: true});
            }
        });
    }

    toggleEnabled = event => {
        const {target: {checked}} = event;
        const {advertisement} = this.state;

        this.updateSettings({...advertisement, enabled: checked});
    };

    updateSettings = advertisement => {
        this.setState({advertisement: {...advertisement}})
    }

    saveSettings = () => {
        const {enabled, skippable, provider, uri, positions} = this.state.advertisement;
        const data = {
            enabled,
            skippable,
            provider,
            uri,
            positions,
        };

        publisherAjax('api/settings/advertisement', {}, 'POST', data).then(result => {
            if (result !== false) {
                showMessage(__('Settings saved', 'isset-video'));
            }
        });
    };

    render() {
        const {advertisement, loaded} = this.state;
        const isEnabled = advertisement && advertisement.enabled;

        if (!loaded) {
            return null;
        }

        return <form className="" onSubmit={e => e.preventDefault()}>
            <div className="isset-video-container mt-20 iv-p-20">
                <div className="video-publisher-flex video-publisher-flex-between iv-w-400">
                    <div>
                        <div>{__('Enable Video Ads', 'isset-video')}</div>
                        <small className="iv-t-orange">
                            {__('Enable or disable ads for your videos', 'isset-video')}.
                        </small>
                    </div>
                    <div>
                        <label className="switch">
                            <input type="checkbox"  value="1" name="enabled" checked={isEnabled} onChange={this.toggleEnabled} />
                            <span className="slider round" />
                        </label>
                    </div>
                </div>
                {isEnabled && <SettingsForm advertisement={advertisement} onUpdate={this.updateSettings} />}
            </div>
            <button className="video-block-button mt-20 isset-video-pointer" type="submit" onClick={this.saveSettings}>{__('Save', 'isset-video')}</button>
        </form>;
    }
}

window.addEventListener('load', () => {
    if (typeof adminpage !== 'undefined' && adminpage === 'videos_page_isset-video-advertisement') {
        const {loggedIn} = window.IssetVideoPublisherAjax;

        if (loggedIn) {
            const adsettingsContainer = document.getElementById('isset-video-advertisement');
            ReactDOM.render(<AdvertisementSettings />, adsettingsContainer);
        }
    }
});