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
        'rgxp': '[^ ]+$',
        'class_input_mirror': 'autosuggester-input-mirror',
        'class_input_mirror_container' : 'autosuggester-input-mirror-container',
        'class_input_mirror_caret': 'autosuggester-input-mirror-caret',
        'class_box': 'autosuggester-box',
        'class_box_container': 'autosuggester-box-container',
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
        this.rgxp = new RegExp(this.options.rgxp, 'i');

        // Turn off the default autocomplete feature
        this.input.set('autocomplete', 'off');

        // Initialize the autocompleter with delay
        // because the tinyMCE needs to get initialized
        setTimeout((function() {
            if (window.tinyMCE) {
                try {
                    this.tinyMCE = tinyMCE.get(this.input.get('id'));
                } catch (err) {}
            }

            // Set the mirror for textarea
            if (this.input.get('tag') == 'textarea' && !this.tinyMCE) {
                this.setUpMirror();
            }

            this.setUpSuggestions();
            this.registerObservers();
        }).bind(this), 500);
    },

    /**
     * Set up the suggestions
     */
    setUpSuggestions: function() {
        var i, position;

        this.box_container = new Element('div', {
            'class': this.options.class_box_container
        });

        this.box = new Element('div', {
            'id': this.input.get('id') + '_autosuggester',
            'class': this.options.class_box
        });

        this.box_list = new Element('ul', {
            'class': this.options.class_box_list
        });

        this.box_list_items = [];
        this.box_list_items_visible = [];

        // Generate the options
        for (i=0; i<this.source.length; i+=1) {
            this.box_list_items[i] = new Element('li', {
                'class': this.options.class_box_list_item,
                'html': '<div class="' + this.options.class_box_list_item_value + '">' + this.source[i]['value'] + '</div><div class="' + this.options.class_box_list_item_content + '">' + this.source[i]['content'] + '</div>'
            }).inject(this.box_list);

            this.box_list_items_visible.push(i);
        }

        this.box_list.inject(this.box);
        this.box.inject(this.box_container);
        this.box_container.inject(document.body);
        this.box_visible = false;
    },

    /**
     * Set up the input mirror
     */
    setUpMirror: function() {
        var copy = ["box-sizing", "font-family", "font-size", "font-style", "font-variant", "font-weight", "height", "letter-spacing", "line-height", "max-height", "min-height", "padding-bottom", "padding-left", "padding-right", "padding-top", "text-decoration", "text-indent", "text-transform", "width", "word-spacing"];
        var styles = window.getComputedStyle(this.input);
        var i;

        this.input_mirror = new Element('div', {
            'class': this.options.class_input_mirror,
            'text': this.input.get('value')
        });

        this.input_mirror_caret = new Element('span', {
            'class': this.options.class_input_mirror_caret,
            'html': '&nbsp;'
        });

        // Clone all styles of an input
        for (i=0; i<copy.length; i++) {
            this.input_mirror.setStyle(copy[i], styles[copy[i]]);
        }

        this.input_mirror_caret.inject(this.input_mirror);
        this.input_mirror.inject(this.input, 'after');
    },

    /**
     * Register the observers
     */
    registerObservers: function() {
        var i;

        // Add the regular events
        if (!this.tinyMCE) {
            this.input.addEvents({
                'keyup': this.eventKeyUp.bind(this),
                'keydown': this.eventKeyDown.bind(this)
            });
        }

        // Add the events to tinyMCE
        if (this.tinyMCE) {
            this.tinyMCE.onKeyUp.add((function(editor, event) {
               this.eventKeyUp.call(this, event);
            }).bind(this));

            // Fix an issue with the "enter" key (see #2)
            this.tinyMCE.onKeyDown.listeners = [];

            this.tinyMCE.onKeyDown.add((function(editor, event) {
                this.eventKeyDown.call(this, event);
            }).bind(this));
        }

        for (i=0; i<this.box_list_items.length; i++) {
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
            var index, value, chunks;
            var position = {x: 0, y: 0};

            // Detect the box position in tinyMCE
            if (this.tinyMCE) {
                position.x = this.tinyMCE.selection.getRng().getClientRects()[0].left;
                position.y = this.tinyMCE.selection.getNode().getClientRects()[0].top + this.tinyMCE.selection.getNode().getClientRects()[0].height;
            } else if (this.input_mirror) {
                // Detect the box position for regular textarea
                index = this.getCaretIndex();
                value = this.input.get('value');
                chunks = [value.substr(0, index), value.substr(index, value.length)];

                // Inject the fake marker at the right position
                this.input_mirror.set('html', '');
                this.input_mirror.grab(document.createTextNode(chunks[0]));
                this.input_mirror.grab(this.input_mirror_caret);
                this.input_mirror.grab(document.createTextNode(chunks[1]));

                position = this.input_mirror_caret.getPosition(this.input_mirror);
                position.y = position.y + this.input_mirror_caret.getSize().y;
            } else {
                // Detect the box position for regular input
                position.y = this.input.getSize().y;
            }

            // Make all list items visible
            for (i=0; i<this.box_list_items.length; i++) {
                this.box_list_items[i].removeClass('invisible');
            }

            // Set the box container position
            if (this.tinyMCE) {
                this.setPosition(this.box_container, $(this.input.get('id') + '_ifr').getPosition());
            } else {
                this.setPosition(this.box_container, this.input.getPosition());
            }

            this.current_list_item = null;
            this.box.setStyle('display', 'block');
            this.setPosition(this.box, position);
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
        for (i=0; i<this.box_list_items.length; i++) {
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

        var value, index, selection, i, match;
        var hide = true;

        // Get the current caret index
        if (this.tinyMCE) {
            selection = this.tinyMCE.selection.getRng();
            value = selection.startContainer.wholeText;
            index = selection.startOffset;

            // Fix the wrong index calculation
            if (value && index == selection.startContainer.length && selection.startContainer.textContent.length != value.length) {
                index = index + (value.length - selection.startContainer.textContent.length);
            }
        } else {
            value = this.input.get('value');
            index = this.getCaretIndex();
        }

        if (!value) {
            this.hideBox();
            return;
        }

        this.filter_text = '';

        value = value.slice(0, index);
        match = this.rgxp.exec(value);

        // Open the box if there is an opening tag
        if (match) {
            this.showBox();

            this.filter_text = match[0];

            if (!this.filterItems()) {
                this.hideBox();
            }
        }
    },

    /**
     * Triggered on the key down
     */
    eventKeyDown: function(event) {
        if (!this.box_visible) {
            return;
        }

        var index = this.box_list_items_visible.indexOf(this.current_list_item);

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

                event.preventDefault();
                this.selectItem();
                break;

            // Highlight the previous item
            case 'up':
                event.preventDefault();

                if (index === -1) {
                    this.highlightItem(this.box_list_items_visible.length - 1);
                } else if (index > 0) {
                    this.highlightItem(this.box_list_items_visible[index - 1]);
                }
                break;

            // Highlight the next item
            case 'down':
                event.preventDefault();

                if (index === -1) {
                    this.highlightItem(this.box_list_items_visible[0]);
                } else if (index < this.box_list_items_visible.length - 1) {
                    this.highlightItem(this.box_list_items_visible[index + 1]);
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
     * @todo the new index is at wrong place in tinyMCE
     */
    selectItem: function() {
        var value, index, index_new;
        var insert = this.source[this.current_list_item]['value'];

        // Replace the filter text if any
/*
        if (this.filter_text.length > 0) {
            insert = insert.substr(this.filter_text.length, insert.length);
        }
*/

        if (this.tinyMCE) {
            this.tinyMCE.selection.setContent(insert);
        } else {
            value = this.input.get('value');
            index = this.getCaretIndex();

            this.input.set('value', value.slice(0, index-this.filter_text.length) + insert + value.slice(index, value.length));
//            this.input.setSelectionRange(index_new, index_new);
        }

        this.hideBox();
    },

    /**
     * Filter the items (return false if there are no items)
     * @return false
     */
    filterItems: function() {
        var i, index;
        var rgxp = new RegExp('^'+this.filter_text+'(?!$)', 'i');

        for (i=0; i<this.box_list_items.length; i++) {
            if (rgxp.test(this.source[i]['value'])) {
                this.box_list_items[i].removeClass('invisible');
                this.box_list_items_visible.include(i);
            } else {
                this.box_list_items[i].addClass('invisible');
                this.box_list_items_visible.erase(i);
            }
        }

        return this.box_list_items_visible.length > 0;
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
    },

    /**
     * Set the position of an element. Used instead of MooTools' setPosition()
     * because of computed position which affects the margins
     * @param object
     * @param object
     */
    setPosition: function(el, position) {
        el.setStyle('left', position.x);
        el.setStyle('top', position.y);
    }
});
