import Tribute from "tributejs/src/Tribute.js";
import 'tributejs/tribute.css';

declare global {
    interface Window { ContaoNotificationCenterAutoSuggester: ContaoNotificationCenterAutoSuggester; }
}

// TODO: Get it to work in tinyMCE
class ContaoNotificationCenterAutoSuggester {
    constructor(widgetId: string, values: Array<{ key: string, value: string }>) {
        let tribute = new Tribute({
            trigger: '##',
            values: values,
            lookup: function (item, mentionText) {
                // Support wildcards (e.g. "form_email" must match "form_*" tokens
                if ('*' === item.name.slice(-1)) {
                    const regex = new RegExp('^' + item.name.slice(0, -1) + '(.+)$');
                    const matches = regex.exec(mentionText);
                    if (null !== matches) {
                        item.customTokenName =  matches[0];
                        return  matches[0];
                    }

                    delete item.customTokenName;
                }

                return item.name;
            },
            selectTemplate: function (item) {
                const token = item.original.customTokenName || item.original.name;

                if ('*' === token.slice(-1)) {
                    return '';
                }

                return '##' + token + '##';
            },
            menuItemTemplate: function (item) {
                return '<strong>##' + item.string + '##</strong><br>' + item.original.label;
            }
        })
        const widgetEl = document.getElementById(widgetId);

        if (null === widgetEl) {
            return;
        }

        tribute.attach(widgetEl);
    }
}

window.ContaoNotificationCenterAutoSuggester = ContaoNotificationCenterAutoSuggester;