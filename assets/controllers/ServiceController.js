import {Controller} from '@hotwired/stimulus';
import axios from "axios";
import {editor, Uri} from 'monaco-editor';
import {setDiagnosticsOptions} from 'monaco-yaml';

export default class ServiceController extends Controller {

    static targets = [
        'editor',
        'button',
    ];

    editor;
    model;
    file;

    connect() {

    }

    execute(event) {
        let target = event.target;
        let url = target.getAttribute('data-url');
        target.setAttribute('loading', 'loading');
        axios.get(url).then((response) => {
            target.removeAttribute('loading');
            this.reaction(response);
        }).catch(console.error);
    }

    edit(event) {
        let target = event.target;
        let file = target.getAttribute('data-file');

        if (this.model) {
            if (this.file === file) {
                this.saveYaml(target);
                this.enableButtons(true);
            }

        } else {
            this.enableButtons(false, target);
            this.editYaml(target);
        }
    }

    editYaml(target) {
        let url = target.getAttribute('data-load-url');
        let file = target.getAttribute('data-file');
        target.setAttribute('loading', 'loading');

        axios.get(url).then((response) => {
            target.removeAttribute('loading');
            target.textContent = file + ' (Save)';
            this.openEditor(file, response.data.yaml);

        }).catch((err) => {
            console.error(err);
            this.closeEditor();
            target.removeAttribute('loading');
            alert('error!');
        });
    }

    saveYaml(target) {
        let file = target.getAttribute('data-file');

        if (confirm('Save to ' + file + '?')) {
            target.setAttribute('loading', 'loading');
            let url = target.getAttribute('data-save-url');
            let data = new FormData();
            data.set('contents', this.model.getValue());

            axios.post(url, data, {headers: {'Content-Type': 'multipart/form-data'}}).then((response) => {
                target.removeAttribute('loading');
                target.textContent = file + ' (Edit)';
                this.closeEditor();
                this.reaction(response);

            }).catch((err) => {
                console.error(err);
                target.removeAttribute('loading');
                alert('error!');
            });
        }
    }

    openEditor(file, yaml) {
        this.editorTarget.style.display = 'block';

        this.file = file;
        this.model = editor.createModel(yaml, 'yaml');
        this.editor = editor.create(document.getElementById('editor'), {
            language: 'yaml',
            model: this.model,
            renderWhitespace: "all",
        });
    }

    closeEditor() {
        if (this.editor) {
            this.editor.dispose();
        }
        this.editor = null;
        this.model = null;
        this.file = null;
        this.editorTarget.style.display = 'none';
        this.enableButtons(true, null);
    }

    reaction(response) {
        let message;
        if (response.data.success) {
            message = 'done!';
        } else {
            message = 'error!';
        }
        if (response.data.message) {
            message = response.data.message;
        }

        alert(message);
    }

    enableButtons(enable, not) {
        this.buttonTargets.forEach((b) => {
            let logic = enable;
            if (b === not) {
                logic = !enable;
            }
            if (logic) {
                b.removeAttribute('disabled');
            } else {
                b.setAttribute('disabled', 'disabled')
            }
        });
    }
}
