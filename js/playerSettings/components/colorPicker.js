import React from 'react';
import PropTypes from 'prop-types';
import {SketchPicker} from 'react-color';

class ColorPicker extends React.Component {
    static propTypes = {
        color: PropTypes.string.isRequired,
        onChange: PropTypes.func.isRequired,
    };

    constructor(props) {
        super(props);

        this.state = {
            showPicker: false,
        };
    }

    toggleShowColorPicker = () => {
        const {showPicker} = this.state;
        this.setState({showPicker: !showPicker});
    };

    render() {
        const {showPicker} = this.state;
        const {color} = this.props;

        return <div>
            <div
                className="selected-color pointer"
                style={{backgroundColor: color}}
                onClick={this.toggleShowColorPicker}
            />
            {showPicker && <div className="colorpicker-container">
                <div className="colorpicker-overlay" onClick={this.toggleShowColorPicker} />
                <SketchPicker color={color} onChange={this.props.onChange} />
            </div>}
        </div>;
    }
}

export default ColorPicker;