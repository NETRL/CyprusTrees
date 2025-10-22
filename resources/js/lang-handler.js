import greek from '../lang/gr.json';
import english from '../lang/en.json';

function __(key) {
    if (this.$page.props.locale === 'en') {
        return english[key] ?? key;
    }

    if (this.$page.props.locale === 'gr') {
        return greek[key] ?? key;
    }

    return key;
}

export {__}