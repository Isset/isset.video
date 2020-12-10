import React from "react";
import {__} from '@wordpress/i18n';
import {createLiveStream, fetchActiveLivestreams, fetchLiveStreamDetails} from './livestream/api/api';

const blocks = window.wp.blocks;

blocks.registerBlockType('isset-video-publisher/livestream-block', {
    title: 'isset.video livestream',
    icon: 'video-alt',
    category: 'embed',
    edit: class extends React.Component {

        constructor(props) {
            super(props);

            this.state = {
                noLivestreamFound: false,
            };
        }

        componentDidMount() {
            const {attributes: {uuid}} = this.props;

            if (!uuid) {
                this.findActiveLivestream();
            }
        }

        componentDidUpdate(prevProps) {
            const {attributes: {uuid}} = this.props;

            if (prevProps.attributes.uuid !== uuid) {
                this.updatedParsedUuid();
            }
        }

        findActiveLivestream = async () => {
            const filtered = await fetchActiveLivestreams();
            if (filtered.length > 0) {
                await this.setState({noLiveStreamFound: false});
                this.findActiveLivestreamDetails(filtered[0].uuid);
            } else {
                this.setState({noLiveStreamFound: true});
            }
        };

        findActiveLivestreamDetails = async uuid => {
            const livestream = await fetchLiveStreamDetails(uuid);

            if (livestream && livestream.uuid) {
                const {setAttributes} = this.props;
                const {uuid, embed_url} = livestream;

                setAttributes({uuid, embedUrl: embed_url});
            }
        }

        createLivestream = async () => {
            const livestream = await createLiveStream();

            if (livestream && livestream.uuid) {
                this.findActiveLivestreamDetails(livestream.uuid);
            }
        }

        updatedParsedUuid = () => {
            const {setAttributes, attributes: {uuid}} = this.props;

            setAttributes({
                uuidParsed: `[isset-livestream uuid=${uuid}]`,
            });
        };

        render() {
            const {attributes: {embedUrl}} = this.props;
            const {noLiveStreamFound} = this.state;

            if (embedUrl) {
                return <div className="isset-video-stream-container">
                    <iframe src={embedUrl} frameBorder="0" width="560" height="315" allowFullScreen />
                </div>;
            } else if (noLiveStreamFound) {
                return <div className="isset-video-stream-container">
                    <div>
                        <h2>{__('No livestream found', 'isset-video')}</h2>

                        <button className="isset-video-btn-orange" onClick={this.createLivestream}>
                            {__('Create Livestream', 'isset-video')}
                        </button>
                    </div>
                </div>;
            }

            return <div className="isset-video-stream-container">
                <h2>{__('Getting livestream data', 'isset-video')}</h2>
            </div>;
        }
    },
    attributes: {
        uuid: {
            type: 'string',
            source: 'attribute',
            attribute: 'data-uuid',
            selector: '.isset-video-stream-container',
        },
        embedUrl: {
            type: 'string',
            source: 'attribute',
            attribute: 'src',
            selector: 'iframe'
        },
    },
    save: props => {
        const {attributes: {embedUrl, uuid}} = props;

        return <div className="isset-video-stream-container" data-uuid={uuid}>
            {embedUrl && <iframe src={embedUrl} frameBorder="0" width="560" height="315" allowFullScreen />}
            {!embedUrl && <h2>{__('No livestream found', 'isset-video')}</h2>}
        </div>;
    },
});