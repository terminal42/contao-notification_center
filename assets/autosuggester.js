/**
 * AutoSuggester plugin
 */
var AutoSuggester = new Class({

    Implements: Options,

    /**
     * Source array:
     * [0] => Array(
     *  'value' => 'cart_html',
     *   'content' => 'Adds the HTML cart.'
     * ),
     * [1] => Array(
     *   'value' => 'cart_text',
     *   'content' => 'Adds the <em>plain text</em> cart.'
     * )
     */

    /**
     * Options
     */
    options: {
        'token': '##',
        'class_box': 'autosuggester-box',
        'class_box_list': 'autosuggester-box-list',
        'class_box_list_item': 'autosuggester-box-list-item',
        'class_box_list_item_active': 'autosuggester-box-list-item-active',
        'class_box_list_item_value': 'autosuggester-box-list-item-value',
        'class_box_list_item_content': 'autosuggester-box-list-item-content',
    },

    /**
     * Keys mapper
     */
    keys_mapper: {
        13: 'enter',
        27: 'esc',
        38: 'up',
        40: 'down'
    },

    /**
     * Initialize the plugin
     * @param object
     * @param array
     * @param object
     */
    initialize: function(el, source, options) {
        this.input = el;
        this.setOptions(options);
        this.source = source;
        this.tinyMCE = false;

        // Turn off the default autocomplete feature
        this.input.set('autocomplete', 'off');

        this.setUpSuggestions();
        this.registerObservers();
    },

    /**
     * Set up the suggestions
     */
    setUpSuggestions: function() {
        var i;

        this.box = new Element('div', {
            'id': this.input.get('id') + '_autosuggester',
            'class': this.options.class_box
        }).setStyle('width', this.input.getSize().x);

        this.box_list = new Element('ul', {
            'class': this.options.class_box_list
        });

        this.box_list_items = [];

        // Generate the options
        for (i=0; i<this.source.length; i+=1) {
            this.box_list_items[i] = new Element('li', {
                'class': this.options.class_box_list_item,
                'html': '<div class="' + this.options.class_box_list_item_value + '">' + this.source[i]['value'] + '</div><div class="' + this.options.class_box_list_item_content + '">' + this.source[i]['content'] + '</div>'
            }).inject(this.box_list);
        }

        this.box_list.inject(this.box);
        this.box.inject(this.input, 'after');
        this.box_visible = false;
    },

    /**
     * Register the observers
     */
    registerObservers: function() {
        var i;

        // Add the events to tinyMCE
        if (window.tinyMCE) {
            setTimeout((function() {
                this.tinyMCE = tinyMCE.get(this.input.get('id'));

                if (!this.tinyMCE) {
                    return;
                }

                this.tinyMCE.onKeyUp.add((function(editor, event) {
                   this.eventKeyUp.call(this, event);
                }).bind(this));

				// Fix an issue with the "enter" key (see #2)
				this.tinyMCE.onKeyDown.listeners = [];

                this.tinyMCE.onKeyDown.add((function(editor, event) {
                    this.eventKeyDown.call(this, event);
                }).bind(this));
            }).bind(this), 1000);
        }

        if (!this.tinyMCE) {
            this.input.addEvents({
                'keyup': this.eventKeyUp.bind(this),
                'keydown': this.eventKeyDown.bind(this)
            });
        }

        for (i=0; i<this.box_list_items.length; i+=1) {
            this.box_list_items[i].addEvents({
                // Highlight the list item
                'mouseenter': (function(event) {
                    this.highlightItem(this.box_list_items.indexOf(event.target));
                }).bind(this),

                // Select the list item
                'click': (function(event) {
                    event.preventDefault();
                    this.selectItem();
                }).bind(this)
            });
        }
    },

    /**
     * Show the box with suggestions
     */
    showBox: function() {
        if (!this.box_visible) {
            this.current_list_item = null;
            this.box.setStyle('display', 'block');
            this.box.scrollTo(0, 0);
            this.box_visible = true;

            // Hide the box if we toggle
            document.body.addEvent('click', this.hideBox.bind(this));
        }
    },

    /**
     * Hide the box with suggestions
     */
    hideBox: function() {
        this.box.setStyle('display', 'none');
        this.box_visible = false;

        // Remove the highlight from all items
        for (i=0; i<this.box_list_items.length; i+=1) {
            this.box_list_items[i].removeClass(this.options.class_box_list_item_active);
        }
    },

    /**
     * Triggered on the key up
     */
    eventKeyUp: function(event) {
        if (this.getKeyCode(event) == 'esc') {
            return;
        }

        var value, index, selection;
        var tokenLength = this.options.token.length;
        var rgxp = new RegExp(this.options.token, 'g');

        if (this.tinyMCE) {
            selection = this.tinyMCE.selection.getRng();
            value = selection.startContainer.wholeText;
            index = selection.startOffset;
        } else {
            value = this.input.get('value');
            index = this.getCaretIndex();
        }

        if (!value) {
            return;
        }

        // Open the box if there is an opening tag
        if (value.substr(index - tokenLength, tokenLength) === this.options.token && ((value.match(rgxp) || []).length % 2) == 1) {
            this.showBox();
        } else {
            this.hideBox();
        }
    },

    /**
     * Triggered on the key down
     */
    eventKeyDown: function(event) {
        if (!this.box_visible) {
            return;
        }

        switch (this.getKeyCode(event)) {
            // Close the box
            case 'esc':
                this.hideBox();
                break;

            // Insert the token
            case 'enter':
                if (this.current_list_item === null) {
                    return;
                }
console.log(this.tinyMCE.onKeyDown.listeners);
                event.preventDefault();
// @todo find a better way to fix the new line problem in tinymce
//this.tinyMCE.execCommand('Delete');
                this.selectItem();
                break;

            // Highlight the previous item
            case 'up':
                event.preventDefault();

                if (this.current_list_item === null) {
                    this.highlightItem(this.box_list_items.length - 1);
                } else if (this.current_list_item > 0) {
                    this.highlightItem(this.current_list_item - 1);
                }
                break;

            // Highlight the next item
            case 'down':
                event.preventDefault();

                if (this.current_list_item === null) {
                    this.highlightItem(0);
                } else if (this.current_list_item < this.box_list_items.length - 1) {
                    this.highlightItem(this.current_list_item + 1);
                }
                break;
        }
    },

    /**
     * Highlight a particular item
     * @param integer
     */
    highlightItem: function(index) {
        var max_height = parseInt(this.box.getStyle('maxHeight'));
        var visible_top = this.box.getScroll().y;
        var visible_bottom = max_height + visible_top;
        var high_top = this.box_list_items[index].getPosition(this.box).y + visible_top;
        var high_bottom = high_top + this.box_list_items[index].getCoordinates().height;

        // Remove the highlight from the current item
        if (this.current_list_item !== null) {
            this.box_list_items[this.current_list_item].removeClass(this.options.class_box_list_item_active);
        }

        this.box_list_items[index].addClass(this.options.class_box_list_item_active);
        this.current_list_item = index;

        // Adjust the scroll position
        if (high_bottom >= visible_bottom) {
            this.box.scrollTo(0, ((high_bottom - max_height > 0) ? (high_bottom - max_height) : 0));
        } else if (high_top < visible_top) {
            this.box.scrollTo(0, high_top);
        }
    },

    /**
     * Select the current item
     */
    selectItem: function() {
        var value, index;

        if (this.tinyMCE) {
            this.tinyMCE.selection.setContent(this.source[this.current_list_item]['value'] + this.options.token);
        } else {
            value = this.input.get('value');
            index = this.getCaretIndex();
            index_new = index + (this.source[this.current_list_item]['value'] + this.options.token).length;

            this.input.set('value', value.slice(0, index) + this.source[this.current_list_item]['value'] + this.options.token + value.slice(index, value.length));
            this.input.setSelectionRange(index_new, index_new);
        }

        this.hideBox();
    },

    /**
     * Get the caret index of an input
     * @return integer
     * @todo check the compatibility with IE
     * @see http://stackoverflow.com/questions/263743/how-to-get-caret-position-in-textarea
     * @see http://stackoverflow.com/questions/3053542/how-to-get-the-start-and-end-points-of-selection-in-text-area
     * @see http://stackoverflow.com/questions/512528/set-cursor-position-in-html-textbox
     */
    getCaretIndex: function() {
        return this.input.selectionStart;
    },

    /**
     * Get the key code
     * @param object
     * @return mixed
     */
    getKeyCode: function(event) {
        var code;

        if (this.tinyMCE) {
            code = event.keyCode;
        } else {
            code = event.event.keyCode;
        }

        return this.keys_mapper[code];
    }
});
