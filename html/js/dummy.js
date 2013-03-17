/**
 * Dummy JS file for smart IDEs like php/webStorm
 *
 * @author  Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

// Basic variables
var baseHref = '';
var csrfToken = '';

// JSON objects
var phpUnderControl = {
    Feed: {
        type: '',
        version: '',
        title: '',
        link: '',
        description: '',
        language: '',
        updated: '',
        items: [
            {
                item: function () {
                    return {
                        title: '',
                        link: '',
                        description: '',
                        updated: '',
                        id: ''
                    }
                }
            }
        ]
    },
    Images: {
        fails: [],
        success: []
    },
    Error: {
        message: '',
        code: '',
        file: '',
        line: '',
        trace: ''
    },
    Settings: {
        feedUrl: '',
        buildsPerRow: '',
        buildClass: '',
        refreshInterval: '',
        projectsToShow: []
    }
};
