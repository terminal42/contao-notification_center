import './autosuggester.scss';

interface ContaoNotificationCenterAutoSuggesterToken {
    name: string;
    label: string;
}

class ContaoNotificationCenterAutoSuggester {
    readonly cssClassInputMirror: string = 'autosuggester-input-mirror';
    readonly cssClassInputMirrorCaret: string = 'autosuggester-input-mirror-caret';
    readonly cssClassBox: string = 'autosuggester-box';
    readonly cssClassBoxContainer: string = 'autosuggester-box-container';
    readonly cssClassBoxList: string = 'autosuggester-box-list';
    readonly cssClassBoxListItem: string = 'autosuggester-box-list-item';
    readonly cssClassBoxListItemActive: string = 'autosuggester-box-list-item-active';
    readonly cssClassBoxListItemValue: string = 'autosuggester-box-list-item-value';
    readonly cssClassBoxListItemContent: string = 'autosuggester-box-list-item-content';

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
        this.tokens = tokens.map(token => ({ name: `##${token.name}##`, label: token.label }));
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

            // Create the mirror for textarea
            if (this.input.tagName.toLowerCase() === 'textarea' && !this.tinyMCEInstance) {
                this.createInputMirror();
            }

            this.createBoxElements();
            this.registerEvents();
        }, 500);
    }

    /**
     * Create the box elements
     */
    createBoxElements(): void {
        this.boxContainer = document.createElement('div');
        this.boxContainer.className = this.cssClassBoxContainer;

        this.box = document.createElement('div');
        this.box.id = `${this.input.id}-autosuggester`;
        this.box.className = this.cssClassBox;

        this.boxList = document.createElement('ul');
        this.boxList.className = this.cssClassBoxList;

        // Generate the options
        this.tokens.forEach((token, index) => {
            this.boxListItems[index] = document.createElement('li');
            this.boxListItems[index].className = this.cssClassBoxListItem;
            this.boxListItems[index].innerHTML = `<div class="${this.cssClassBoxListItemValue}">${token.name}</div><div class="${this.cssClassBoxListItemContent}">${token.label}</div>`;

            this.boxList.appendChild(this.boxListItems[index]);

            this.boxListItemsVisibleIndexes.push(index);
        });

        this.box.appendChild(this.boxList);
        this.boxContainer.appendChild(this.box);
        document.body.appendChild(this.boxContainer);

        this.boxVisible = false;
    }

    /**
     * Create the input mirror
     */
    createInputMirror(): void {
        this.inputMirror = document.createElement('div');
        this.inputMirror.className = this.cssClassInputMirror;
        this.inputMirror.innerText = this.input.value;

        this.inputMirrorCaret = document.createElement('span');
        this.inputMirrorCaret.className = this.cssClassInputMirrorCaret;
        this.inputMirrorCaret.innerHTML = '&nbsp;';

        // Clone all styles of an input
        const properties = ['box-sizing', 'font-family', 'font-size', 'font-style', 'font-variant', 'font-weight', 'height', 'letter-spacing', 'line-height', 'max-height', 'min-height', 'padding-bottom', 'padding-left', 'padding-right', 'padding-top', 'text-decoration', 'text-indent', 'text-transform', 'width', 'word-spacing'];
        const styles = window.getComputedStyle(this.input);

        properties.forEach(property => this.inputMirror.style[property] = styles[property]);

        // Add the elements to DOM
        this.inputMirror.appendChild(this.inputMirrorCaret);
        this.input.insertAdjacentElement('afterend', this.inputMirror);
    }

    /**
     * Register the events
     */
    registerEvents(): void {
        // Add the regular events
        if (!this.tinyMCEInstance) {
            this.input.addEventListener('keyup', (e: KeyboardEvent) => this.onKeyUpEvent(e));
            this.input.addEventListener('keydown', (e: KeyboardEvent) => this.onKeyDownEvent(e));
        }

        // Add the events to tinyMCE
        if (this.tinyMCEInstance) {
            if (typeof this.tinyMCEInstance.on === 'function') {
                this.tinyMCEInstance.on('keyUp', (e: KeyboardEvent) => this.onKeyUpEvent(e));

                // Fix an issue with the "enter" key
                this.tinyMCEInstance.off('keyDown');
                this.tinyMCEInstance.on('keyDown', (e: KeyboardEvent) => this.onKeyDownEvent(e));
            } else {
                this.tinyMCEInstance.onKeyUp.add((editor, event: KeyboardEvent) => this.onKeyUpEvent(event));

                // Fix an issue with the "enter" key
                this.tinyMCEInstance.onKeyDown.listeners = [];
                this.tinyMCEInstance.onKeyDown.add((editor, event: KeyboardEvent) => this.onKeyDownEvent(event));
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
     * Show the box with tokens
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
            index = this.input.selectionStart;
            value = this.input.value;
            chunks = [value.substring(0, index), value.substring(index, value.length)];

            // Inject the fake marker at the right position
            this.inputMirror.innerHTML = '';
            this.inputMirror.appendChild(document.createTextNode(chunks[0]));
            this.inputMirror.appendChild(this.inputMirrorCaret);
            this.inputMirror.appendChild(document.createTextNode(chunks[1]));

            position = this.getPosition(this.inputMirrorCaret, this.inputMirror);
            position.top += this.inputMirrorCaret.getBoundingClientRect().height;
        } else {
            // Detect the box position for regular input
            position.top = this.input.getBoundingClientRect().height;
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
     * Hide the box with tokens
     */
    hideBox(): void {
        this.box.style.display = 'none';
        this.boxVisible = false;

        // Remove the highlight from all items
        this.boxListItems.forEach(item => item.classList.remove(this.cssClassBoxListItemActive));
    }

    /**
     * On the key up event
     */
    onKeyUpEvent(event: KeyboardEvent): void {
        if (event.key === 'Escape' || event.key === 'ArrowUp' || event.key === 'ArrowDown') {
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
            index = this.input.selectionStart;
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
     * On the key up event
     */
    onKeyDownEvent(event: KeyboardEvent): void {
        if (!this.boxVisible) {
            return;
        }

        const index = this.boxListItemsVisibleIndexes.indexOf(this.currentListItemIndex);

        switch (event.key) {
            // Close the box
            case 'Escape':
                this.hideBox();
                break;

            // Insert the token
            case 'Enter':
                if (this.currentListItemIndex === null) {
                    return;
                }

                event.preventDefault();
                this.selectItem();
                break;

            // Highlight the previous item
            case 'ArrowUp':
                event.preventDefault();

                if (index === -1) {
                    this.highlightItem(this.boxListItemsVisibleIndexes.length - 1);
                } else if (index > 0) {
                    this.highlightItem(this.boxListItemsVisibleIndexes[index - 1]);
                }
                break;

            // Highlight the next item
            case 'ArrowDown':
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
     * Highlight an item with provided index
     */
    highlightItem(index: number): void {
        const maxHeight = parseInt(window.getComputedStyle(this.box).maxHeight);
        const visibleTop = this.box.scrollTop;
        const visibleBottom = maxHeight + visibleTop;
        const highTop = this.getPosition(this.boxListItems[index], this.box).top + visibleTop;
        const highBottom = highTop + this.boxListItems[index].getBoundingClientRect().height;

        // Remove the highlight from the current item
        if (this.currentListItemIndex !== null) {
            this.boxListItems[this.currentListItemIndex].classList.remove(this.cssClassBoxListItemActive);
        }

        this.boxListItems[index].classList.add(this.cssClassBoxListItemActive);
        this.currentListItemIndex = index;

        // Adjust the scroll position
        if (highBottom >= visibleBottom) {
            this.box.scrollTo(0, ((highBottom - maxHeight > 0) ? (highBottom - maxHeight) : 0));
        } else if (highTop < visibleTop) {
            this.box.scrollTo(0, highTop);
        }
    }

    /**
     * Select the currently selected item
     */
    selectItem(): void {
        let value, index, indexNew;
        let insert = this.tokens[this.currentListItemIndex]['name'];

        // Replace the filter text if any
        if (this.filterText.length > 0) {
            insert = insert.substring(this.filterText.length, insert.length);
        }

        if (this.tinyMCEInstance) {
            this.tinyMCEInstance.selection.setContent(insert);
        } else {
            value = this.input.value;
            index = this.input.selectionStart;
            indexNew = index + insert.length;

            this.input.value = value.slice(0, index) + insert + value.slice(index, value.length);
            this.input.setSelectionRange(indexNew, indexNew);
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

                if (!this.boxListItemsVisibleIndexes.includes(index)) {
                    this.boxListItemsVisibleIndexes.push(index);
                }
            } else {
                this.boxListItems[index].classList.add('invisible');

                if (this.boxListItemsVisibleIndexes.includes(index)) {
                    this.boxListItemsVisibleIndexes.splice(this.boxListItemsVisibleIndexes.indexOf(index), 1);
                }
            }
        });

        return this.boxListItemsVisibleIndexes.length > 0;
    }

    /**
     * Get the position of an element
     */
    getPosition(el: HTMLElement, relative: HTMLElement|null = null): any {
        const position = el.getBoundingClientRect();
        let top = position.top + window.scrollY;
        let left = position.left + window.scrollX;

        if (relative !== null) {
            const relativePosition = this.getPosition(relative);

            top -= relativePosition.top;
            left -= relativePosition.left;
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

window.initContaoNotificationCenterAutoSuggester = (input: HTMLElement|string, tokens: ContaoNotificationCenterAutoSuggesterToken[]) => new ContaoNotificationCenterAutoSuggester(input, tokens);
