import React from 'react';
import ReactDOM from 'react-dom';
import {publisherAjax} from '../../ajax';
import {DEFAULT_PROVIDER, getExample} from '../helpers/advertisement';
import {__} from '@wordpress/i18n';
import {showMessage} from '../../toast/helper/toast';

class AdvertisementSettings extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            loaded: false,
            enabled: true,
            skippable: true,
            providers: [],
            provider: {},
            uri: '',
            positions: ['before'],
        };
    }

    componentDidMount() {
        publisherAjax('api/advertisement/providers').then(result => {
            this.setState({providers: result});
        });

        publisherAjax('api/settings/advertisement').then(result => {
            const {provider, uri, positions, enabled} = result;

            this.setState({enabled, provider: provider.id, uri, positions, loaded: true});
        });
    }

    toggleEnabled = event => {
        const {target: {checked}} = event;

        this.setState({enabled: checked});
    };

    toggleSkippable = event => {
        const {target: {checked}} = event;

        this.setState({skippable: checked});
    }

    setProvider = event => {
        const {target: {value}} = event;

        this.setState({provider: value});
    };

    setUri = event => {
        const {target: {value}} = event;

        this.setState({uri: value});
    };

    togglePosition = event => {
        const {target: {checked, value}} = event;
        const {positions} = this.state;
        const index = positions.indexOf(value);

        if (checked && index === -1) {
            positions.push(value);
        } else if (index >= 0) {
            positions.splice(index, 1);
        }

        this.setState({positions});
    };

    saveSettings = () => {
        const {enabled, skippable, provider, uri, positions} = this.state;
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
        const {enabled, providers, provider, uri, positions = [], loaded, skippable} = this.state;
        const providerId = provider ? parseInt(provider) : DEFAULT_PROVIDER;
        const selectedProvider = providers.filter(provider => provider.id === providerId).pop() || DEFAULT_PROVIDER;

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
                            <input type="checkbox"  value="1" name="enabled" checked={enabled} onChange={this.toggleEnabled} />
                            <span className="slider round" />
                        </label>
                    </div>
                </div>
                {enabled && <>
                    <div className="iv-w-100 mt-20">
                        <div className="">
                            <div>
                                <div><label htmlFor="ad_provider">{__('Ad Source', 'isset-video')}</label></div>
                                <small className="iv-t-orange">
                                    {__('Choose your Ad Server', 'isset-video')}
                                </small>
                            </div>
                        </div>
                        <div className="mt-3">
                            <div className="form-group">
                                <select id="ad_provider" className="iv-w-100 mt-20" name="provider" onChange={this.setProvider} defaultValue={providerId}>
                                    {providers.map(provider => <option key={`provider-option-${provider.id}`} value={provider.id} >
                                        {provider.name}
                                    </option>)}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div className="iv-w-100 mt-20">
                        <div className="d-flex justify-content-between">
                            <div>
                                <div><label htmlFor="ad_provider">Identifier</label></div>
                                <small className="iv-t-orange">
                                    {getExample(selectedProvider.key)}
                                </small>
                            </div>
                        </div>
                        <div className="mt-3">
                            <input className="iv-w-400 mt-20" type="text" placeholder={__('Ad Uri', 'isset-video')} name="uri" onChange={this.setUri} value={uri} />
                        </div>
                    </div>
                    {providerId === DEFAULT_PROVIDER && <>
                        <div className="video-publisher-flex video-publisher-flex-between iv-w-400 mt-20">
                            <div>
                                <div>{__('Ads are Skippable', 'isset-video')}</div>
                                <small className="iv-t-orange">
                                    {__('By enabling this, the shown ads will be skippable by the user', 'isset-video')}.
                                </small>
                            </div>
                            <div>
                                <label className="switch">
                                    <input type="checkbox" checked={skippable} name="skippable" onChange={this.toggleSkippable}/>
                                    <span className="slider round" />
                                </label>
                            </div>
                        </div>
                        <div className="iv-w-100 mt-20">
                            <div className="d-flex justify-content-between">
                                <div>
                                    <div><label htmlFor="ad_provider">{__('Ad Location', 'isset-video')}</label></div>
                                    <small className="iv-t-orange">
                                        {__('Choose if your ads should be played before, after, or/and during video playback', 'isset-video')}.
                                    </small>
                                </div>
                            </div>
                            <div className="mt-3">
                                <div className="video-publisher-flex video-publisher-flex-between iv-w-400 mt-20">
                                    <div>
                                        <div>{__('Before', 'isset-video')}</div>
                                    </div>
                                    <div>
                                        <label className="switch">
                                            <input type="checkbox" value="before" checked={positions.includes('before')}
                                                   name="positions[before]" onChange={this.togglePosition}/>
                                            <span className="slider round" />
                                        </label>
                                    </div>
                                </div>
                                <div className="video-publisher-flex video-publisher-flex-between iv-w-400 mt-20">
                                    <div>
                                        <div>{__('After', 'isset-video')}</div>
                                    </div>
                                    <div>
                                        <label className="switch">
                                            <input type="checkbox" value="after" checked={positions.includes('after')}
                                                   name="positions[after]" onChange={this.togglePosition}/>
                                            <span className="slider round" />
                                        </label>
                                    </div>
                                </div>
                                <div className="video-publisher-flex video-publisher-flex-between iv-w-400 mt-20">
                                    <div>
                                        <div>{__('Middle', 'isset-video')}</div>
                                    </div>
                                    <div>
                                        <label className="switch">
                                            <input type="checkbox" value="middle" checked={positions.includes('middle')}
                                                   name="positions[middle]" onChange={this.togglePosition} />
                                            <span className="slider round" />
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </>}
                </>}
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