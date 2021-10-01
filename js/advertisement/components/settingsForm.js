import React from 'react';
import {publisherAjax} from '../../ajax';
import {__} from '@wordpress/i18n';
import {DEFAULT_PROVIDER, getExample} from '../helpers/advertisement';

class SettingsForm extends React.Component {
    constructor(props) {
        super(props);

        const {enabled = false, skippable = true, provider = DEFAULT_PROVIDER, uri = '', positions = ['before']} = props.advertisement;

        this.state = {
            providers: [],
            advertisement: {
                enabled,
                skippable,
                provider,
                uri,
                positions,
            },
        };
    }

    componentDidMount() {
        publisherAjax('api/advertisement/providers').then(result => {
            this.setState({providers: result});
        });
    }

    componentDidUpdate(prevProps) {
        const {advertisement} = this.props;

        if (advertisement !== prevProps.advertisement) {
            this.setState({advertisement: this.mergeChanges(advertisement)});
        }
    }

    update = newData => {
        const advertisement = this.mergeChanges(newData);
        this.setState({advertisement});
        this.props.onUpdate(advertisement);
    };

    mergeChanges = newData => {
        return {...this.state.advertisement, ...newData};
    };

    toggleSkippable = event => {
        const {target: {checked}} = event;

        this.update({skippable: checked});
    };

    setProvider = event => {
        const {target: {value}} = event;

        this.update({provider: value});
    };

    setUri = event => {
        const {target: {value}} = event;

        this.update({uri: value});
    };

    togglePosition = event => {
        const {target: {checked, value}} = event;
        const {advertisement: {positions}} = this.state;
        const index = positions.indexOf(value);

        if (checked && index === -1) {
            positions.push(value);
        } else if (index >= 0) {
            positions.splice(index, 1);
        }

        this.update({positions});
    };

    render() {
        const {providers, advertisement: {skippable, provider, uri, positions}} = this.state;
        const providerId = provider ? parseInt(provider) : DEFAULT_PROVIDER;
        const selectedProvider = providers.filter(provider => provider.id === providerId).pop() || DEFAULT_PROVIDER;

        return <>
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
                        <select id="ad_provider" className="iv-w-100 mt-20" name="provider" onChange={this.setProvider} value={providerId}>
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
        </>;
    }
}

export default SettingsForm;