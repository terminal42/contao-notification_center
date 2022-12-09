interface ContaoNotificationCenterAutoSuggesterToken {
    name: string;
    label: string;
}

class ContaoNotificationCenterAutoSuggester {
    readonly cssClassInputMirror: 'autosuggester-input-mirror';
    readonly cssClassInputMirrorCaret: 'autosuggester-input-mirror-caret';
    readonly cssClassBox: 'autosuggester-box';
    readonly cssClassBoxContainer: 'autosuggester-box-container';
    readonly cssClassBoxList: 'autosuggester-box-list';
    readonly cssClassBoxListItem: 'autosuggester-box-list-item';
    readonly cssClassBoxListItemActive: 'autosuggester-box-list-item-active';
    readonly cssClassBoxListItemValue: 'autosuggester-box-list-item-value';
    readonly cssClassBoxListItemContent: 'autosuggester-box-list-item-content';

    box: HTMLElement;
    boxContainer: HTMLElement;
    boxList: HTMLElement;
    boxListItems: HTMLElement[] = [];
    boxListItemsVisibleIndexes: number[] = [];
    boxVisible: boolean;

    currentListItemIndex: number|null;

    filterText: string;

    input: HTMLInputElement|HTMLTextAreaElement;
    inputMirror: HTMLElement;
    inputMirrorCaret: HTMLElement;

    rgxp: RegExp;

    tinyMCEInstance: any;

    tokens: ContaoNotificationCenterAutoSuggesterToken[];

    constructor(input: HTMLElement|string, tokens: ContaoNotificationCenterAutoSuggesterToken[]) {
        this.input = ((typeof input === 'string') ? document.getElementById(input) : input) as HTMLInputElement|HTMLTextAreaElement;
        this.tokens = tokens;
        this.tinyMCEInstance = false;
        this.rgxp = new RegExp('[^ |\n]+$', 'i');

        // Turn off the default autocomplete feature
        this.input.autocomplete = 'off';

        // Initialize with delay because tinyMCE needs to get initialized first
        setTimeout(() => {
            if (window.tinyMCE) {
                try {
                    this.tinyMCEInstance = window.tinyMCE.get(this.input.id);
                } catch (err) {}
            }

            // Set the mirror for textarea
            if (this.input.tagName.toLowerCase() === 'textarea' && !this.tinyMCEInstance) {
                this.setUpMirror();
            }

            this.setUpSuggestions();
            this.registerObservers();
        }, 500);
    }

    /**
     * Set up the suggestions
     */
    setUpSuggestions(): void {
        this.boxContainer = document.createElement('div');
        this.boxContainer.className = this.cssClassBoxContainer;

        this.box = document.createElement('div');
        this.box.id = `${this.input.id}_autosuggester`;
        this.box.className = this.cssClassBox;

        this.boxList = document.createElement('ul');
        this.boxList.className = this.cssClassBoxList;

        // Generate the options
        this.tokens.forEach((item, index) => {
            this.boxListItems[index] = document.createElement('li');
            this.boxListItems[index].className = this.cssClassBoxListItem;
            this.boxListItems[index].innerHTML = `<div class="${this.cssClassBoxListItemValue}">${item.name}</div><div class="${this.cssClassBoxListItemContent}">${item.label}</div>`;

            this.boxListItemsVisibleIndexes.push(index);
        });

        this.box.appendChild(this.boxList);
        this.boxContainer.appendChild(this.box);
        document.body.appendChild(this.boxContainer);

        this.boxVisible = false;
    }

    /**
     * Set up the input mirror
     */
    setUpMirror(): void {
        this.inputMirror = document.createElement('div');
        this.inputMirror.className = this.cssClassInputMirror;
        this.inputMirror.innerText = this.input.value;

        this.inputMirrorCaret = document.createElement('span');
        this.inputMirrorCaret.className = this.cssClassInputMirrorCaret;
        this.inputMirrorCaret.innerHTML = '&nbsp;';

        // Clone all styles of an input
        const properties = ['box-sizing', 'font-family', 'font-size', 'font-style', 'font-variant', 'font-weight', 'height', 'letter-spacing', 'line-height', 'max-height', 'min-height', 'padding-bottom', 'padding-left', 'padding-right', 'padding-top', 'text-decoration', 'text-indent', 'text-transform', 'width', 'word-spacing'];
        const styles = window.getComputedStyle(this.input);

        properties.forEach(property => this.inputMirror.style[property] = styles);

        // Add the elements to DOM
        this.inputMirror.appendChild(this.inputMirrorCaret);
        this.input.insertAdjacentElement('afterend', this.inputMirror);
    }

    /**
     * Register the observers
     */
    registerObservers(): void {
        // Add the regular events
        if (!this.tinyMCEInstance) {
            this.input.addEventListener('keyup', e => this.eventKeyUp(e));
            this.input.addEventListener('keydown', e => this.eventKeyDown(e));
        }

        // Add the events to tinyMCE
        if (this.tinyMCEInstance) {
            if (typeof this.tinyMCEInstance.on === 'function') {
                this.tinyMCEInstance.on('keyUp', e => this.eventKeyUp(e));

                // Fix an issue with the "enter" key
                this.tinyMCEInstance.off('keyDown');
                this.tinyMCEInstance.on('keyDown', e => this.eventKeyDown(e));
            } else {
                this.tinyMCEInstance.onKeyUp.add((editor, event) => this.eventKeyUp(event));

                // Fix an issue with the "enter" key
                this.tinyMCEInstance.onKeyDown.listeners = [];
                this.tinyMCEInstance.onKeyDown.add((editor, event) => this.eventKeyDown(event));
            }
        }

        this.boxListItems.forEach(item => {
            // Highlight the list item
            item.addEventListener('mouseenter', (e: any) => this.highlightItem(this.boxListItems.indexOf(e.target)));

            // Select the list item
            item.addEventListener('click', e => {
                e.preventDefault();
                this.selectItem();
            });
        });
    }

    /**
     * Show the box with suggestions
     */
    showBox(): void {
        if (this.boxVisible) {
            return;
        }

        let index, value, chunks;
        let position = {left: 0, top: 0};

        // Detect the box position in tinyMCE
        if (this.tinyMCEInstance) {
            position.left = this.tinyMCEInstance.selection.getRng().getClientRects()[0].left;
            position.top = this.tinyMCEInstance.selection.getNode().getClientRects()[0].top + this.tinyMCEInstance.selection.getNode().getClientRects()[0].height;
        } else if (this.inputMirror) {
            // Detect the box position for regular textarea
            index = this.getCaretIndex();
            value = this.input.value;
            chunks = [value.substring(0, index), value.substring(index, value.length)];

            // Inject the fake marker at the right position
            this.inputMirror.innerHTML = '';
            this.inputMirror.appendChild(document.createTextNode(chunks[0]));
            this.inputMirror.appendChild(this.inputMirrorCaret);
            this.inputMirror.appendChild(document.createTextNode(chunks[1]));

            position = this.getPosition(this.inputMirrorCaret, this.inputMirror);
            position.top = position.top + this.inputMirrorCaret.offsetHeight;
        } else {
            // Detect the box position for regular input
            position.top = this.input.offsetHeight;
        }

        // Make all list items visible
        this.boxListItems.forEach(item => item.classList.remove('invisible'));

        // Set the box container position
        if (this.tinyMCEInstance) {
            this.setPosition(this.boxContainer, this.getPosition(document.getElementById(`${this.input.id}_ifr`)));
        } else {
            this.setPosition(this.boxContainer, this.getPosition(this.input));
        }

        this.currentListItemIndex = null;
        this.box.style.display = 'block';
        this.setPosition(this.box, position);
        this.box.scrollTo(0, 0);
        this.boxVisible = true;

        // Hide the box if we toggle
        document.body.addEventListener('click', () => this.hideBox());
    }

    /**
     * Hide the box with suggestions
     */
    hideBox(): void {
        this.box.style.display = 'none';
        this.boxVisible = false;

        // Remove the highlight from all items
        this.boxListItems.forEach(item => item.classList.remove(this.cssClassBoxListItemActive));
    }

    /**
     * Triggered on the key up
     */
    eventKeyUp(event: Event): void {
        if (this.getKeyCode(event) === 'esc') {
            return;
        }

        let value, index, selection, match;

        // Get the current caret index
        if (this.tinyMCEInstance) {
            selection = this.tinyMCEInstance.selection.getRng();
            value = selection.startContainer.wholeText;
            index = selection.startOffset;

            // Fix the wrong index calculation
            if (value && index === selection.startContainer.length && selection.startContainer.textContent.length !== value.length) {
                index = index + (value.length - selection.startContainer.textContent.length);
            }
        } else {
            value = this.input.value;
            index = this.getCaretIndex();
        }

        if (!value) {
            this.hideBox();
            return;
        }

        this.filterText = '';

        value = value.slice(0, index);
        match = this.rgxp.exec(value);

        // Open the box if there is an opening tag
        if (match) {
            this.showBox();
            this.filterText = match[0];

            if (!this.filterItems()) {
                this.hideBox();
            }
        }
    }

    /**
     * Triggered on the key down
     */
    eventKeyDown(event: Event): void {
        if (!this.boxVisible) {
            return;
        }

        const index = this.boxListItemsVisibleIndexes.indexOf(this.currentListItemIndex);

        switch (this.getKeyCode(event)) {
            // Close the box
            case 'esc':
                this.hideBox();
                break;

            // Insert the token
            case 'enter':
                if (this.currentListItemIndex === null) {
                    return;
                }

                event.preventDefault();
                this.selectItem();
                break;

            // Highlight the previous item
            case 'up':
                event.preventDefault();

                if (index === -1) {
                    this.highlightItem(this.boxListItemsVisibleIndexes.length - 1);
                } else if (index > 0) {
                    this.highlightItem(this.boxListItemsVisibleIndexes[index - 1]);
                }
                break;

            // Highlight the next item
            case 'down':
                event.preventDefault();

                if (index === -1) {
                    this.highlightItem(this.boxListItemsVisibleIndexes[0]);
                } else if (index < this.boxListItemsVisibleIndexes.length - 1) {
                    this.highlightItem(this.boxListItemsVisibleIndexes[index + 1]);
                }
                break;
        }
    }

    /**
     * Highlight a particular item
     */
    highlightItem(index: number): void {
        const max_height = parseInt(this.box.style.maxHeight);
        const visible_top = this.box.scrollTop;
        const visible_bottom = max_height + visible_top;
        const high_top = this.getPosition(this.boxListItems[index], this.box).top + visible_top;
        const high_bottom = high_top + this.boxListItems[index].offsetHeight;

        // Remove the highlight from the current item
        if (this.currentListItemIndex !== null) {
            this.boxListItems[this.currentListItemIndex].classList.remove(this.cssClassBoxListItemActive);
        }

        this.boxListItems[index].classList.add(this.cssClassBoxListItemActive);
        this.currentListItemIndex = index;

        // Adjust the scroll position
        if (high_bottom >= visible_bottom) {
            this.box.scrollTo(0, ((high_bottom - max_height > 0) ? (high_bottom - max_height) : 0));
        } else if (high_top < visible_top) {
            this.box.scrollTo(0, high_top);
        }
    }

    /**
     * Select the current item
     */
    selectItem(): void {
        let value, index, index_new;
        let insert = this.tokens[this.currentListItemIndex]['name'];

        // Replace the filter text if any
        if (this.filterText.length > 0) {
            insert = insert.substring(this.filterText.length, insert.length);
        }

        if (this.tinyMCEInstance) {
            this.tinyMCEInstance.selection.setContent(insert);
        } else {
            value = this.input.value;
            index = this.getCaretIndex();
            index_new = index + insert.length;

            this.input.value = value.slice(0, index) + insert + value.slice(index, value.length);
            this.input.setSelectionRange(index_new, index_new);
        }

        this.hideBox();
    }

    /**
     * Filter the items (return false if there are no items)
     */
    filterItems(): boolean {
        const rgxp = new RegExp(`^${this.filterText}(?!$)`, 'i');

        this.boxListItems.forEach((item, index) => {
            if (rgxp.test(this.tokens[index]['name'])) {
                this.boxListItems[index].classList.remove('invisible');
                this.boxListItemsVisibleIndexes.push(index);
            } else {
                this.boxListItems[index].classList.add('invisible');
                this.boxListItemsVisibleIndexes.splice(this.boxListItemsVisibleIndexes.indexOf(index), 1);
            }
        });

        return this.boxListItemsVisibleIndexes.length > 0;
    }

    /**
     * Get the caret index of an input
     * @see http://stackoverflow.com/questions/263743/how-to-get-caret-position-in-textarea
     * @see http://stackoverflow.com/questions/3053542/how-to-get-the-start-and-end-points-of-selection-in-text-area
     * @see http://stackoverflow.com/questions/512528/set-cursor-position-in-html-textbox
     */
    getCaretIndex(): number {
        return this.input.selectionStart;
    }

    /**
     * Get the key code
     */
    getKeyCode(event: any): string|undefined {
        let code;

        if (this.tinyMCEInstance) {
            code = event.keyCode;
        } else {
            code = event.event.keyCode;
        }

        // TODO: check if we can just use the modern event.key
        const mapper = {
            13: 'enter',
            27: 'esc',
            38: 'up',
            40: 'down'
        };

        return mapper[code];
    }

    /**
     * Get the position of an element
     */
    getPosition(el: HTMLElement, relative: HTMLElement|null = null): any {
        // TODO: perhaps include scrolls?
        // @see https://github.com/mootools/mootools-core/blob/187a16bae2d7144ea4b81aa5c0586498f0fe17c7/Source/Element/Element.Dimensions.js#L177

        const position = el.getBoundingClientRect();
        let top = position.top;
        let left = position.left;

        if (relative !== null) {
            const relativePosition = this.getPosition(relative);

            top = position.top - relativePosition.top; // TODO: - top border?;
            left = position.left - relativePosition.left;  // TODO: - left border?
        }

        return {top, left};
    }

    /**
     * Set the position of an element
     */
    setPosition(el: HTMLElement, position: any): void {
        el.style.left = `${position.left}px`;
        el.style.top = `${position.top}px`;
    }
}
