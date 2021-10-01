import React from 'react';
import {publisherAjax} from '../../ajax';
import {DEFAULT_ADVERTISEMENT, DEFAULT_PROVIDER} from '../helpers/advertisement';
import {showMessage} from '../../toast/helper/toast';
import {__} from '@wordpress/i18n';
import SettingsForm from './settingsForm';

class PublishAdvertisementSettings extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            customize: false,
            loaded: false,
            enabled: true,
            advertisement: DEFAULT_ADVERTISEMENT,
        };
    }

    componentDidMount() {
        const {uuid} = this.props;

        publisherAjax(`api/publishes/${uuid}/advertisement`).then(result => {
            if (result && !Array.isArray(result)) {
                const {enabled, skippable, provider, uri, positions} = result;
                this.setState({
                    advertisement: {
                        enabled,
                        skippable,
                        uri,
                        positions,
                        provider: provider ? provider.id : DEFAULT_PROVIDER,
                    }, loaded: true, customize: true}
                );
            } else {
                this.setState({loaded: true, customize: false});
            }
        });
    }

    toggleEnabled = event => {
        const {target: {checked}} = event;
        const {advertisement} = this.state;

        this.updateSettings({...advertisement, enabled: checked});
    };

    toggleCustomize = event => {
        const {target: {checked}} = event;

        if (!checked) {
            this.deletePublishAdvertisementSettings();
        }

        this.setState({customize: checked});
    }

    updateSettings = advertisement => {
        this.setState({advertisement: {...advertisement}})
    };

    deletePublishAdvertisementSettings = () => {
        const {uuid} = this.props;

        publisherAjax(`api/publishes/${uuid}/advertisement`, {}, 'DELETE');
    };

    saveSettings = () => {
        const {uuid} = this.props;
        const {enabled, skippable, provider, uri, positions} = this.state.advertisement;
        const data = {
            enabled,
            skippable,
            provider,
            uri,
            positions,
        };

        publisherAjax(`api/publishes/${uuid}/advertisement`, {}, 'POST', data).then(result => {
            if (result !== false) {
                showMessage(__('Settings saved', 'isset-video'));
            }
        });
    };

    render() {
        const {advertisement, customize, loaded} = this.state;
        const isEnabled = advertisement && advertisement.enabled;

        if (!loaded) {
            return null;
        }

        return <div className="isset-video-overlay video-publisher-admin">
            <div className="isset-video-custom-still-dialog">
                <span className="isset-video-close dashicons dashicons-no" onClick={this.props.onClose} />
                <h2 className="video-publisher-text-white">Video Advertisement Settings</h2>
                <div className="isset-video-container mt-20 iv-p-20">
                    <div className="video-publisher-flex video-publisher-flex-between iv-w-400">
                        <div>
                            <div>{__('Set Video Advertisement Settings', 'isset-video')}</div>
                            <small className="iv-t-orange">
                                {__('Set custom advertisement settings for this video. By using this, you can enable/disable ads specifically for this video.', 'isset-video')}.
                            </small>
                        </div>
                        <div>
                            <label className="switch">
                                <input type="checkbox"  value="1" name="enabled" checked={customize} onChange={this.toggleCustomize} />
                                <span className="slider round" />
                            </label>
                        </div>
                    </div>


                    {customize && <>
                        <div className="video-publisher-flex video-publisher-flex-between iv-w-400">
                            <div>
                                <div>{__('Enable Video Ads', 'isset-video')}</div>
                                <small className="iv-t-orange">
                                    {__('Enable or disable ads for this video', 'isset-video')}.
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
                    </>}
                </div>
                <div className="mt-20">
                    <button className="isset-video-btn video-publisher-mr-2" type="submit" onClick={this.saveSettings}>{__('Save', 'isset-video')}</button>
                    <button className="isset-video-btn btn-danger" onClick={this.props.onClose}>{__('Cancel', 'isset-video')}</button>
                </div>

            </div>
        </div>;
    }
}

export default PublishAdvertisementSettings;