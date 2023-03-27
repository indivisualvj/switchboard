import {Controller} from '@hotwired/stimulus';
import axios from "axios";
import {editor} from 'monaco-editor';

export default class ServiceController extends Controller {

    static targets = [
        'editor',
        'button',
        'status',
    ];

    editor;
    model;
    file;

    connect() {
        this.initStatus();
    }

    execute(event) {
        let target = event.target;
        let url = target.getAttribute('data-url');
        this.setLoading(target, true);
        this.enableButtons(false, target);

        axios.get(url).then((response) => {
            this.setLoading(target, false);
            this.enableButtons(true);
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
        this.setLoading(target, true);

        axios.get(url).then((response) => {
            this.setLoading(target, false);
            target.textContent = file + ' (Save)';
            this.openEditor(file, response.data.yaml);

        }).catch((err) => {
            console.error(err);
            this.closeEditor();
            this.setLoading(target, false);
            alert('error!');
        });
    }

    saveYaml(target) {
        let file = target.getAttribute('data-file');

        if (confirm('Save to ' + file + '?')) {
            this.setLoading(target, true);

            let url = target.getAttribute('data-save-url');
            let data = new FormData();
            data.set('contents', this.model.getValue());

            axios.post(url, data, {headers: {'Content-Type': 'multipart/form-data'}}).then((response) => {
                this.setLoading(target, false);
                target.textContent = file + ' (Edit)';
                this.closeEditor();
                this.reaction(response);

            }).catch((err) => {
                console.error(err);
                this.setLoading(target, false);
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
            return;
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
            this.setEnabled(b, logic);
        });
    }

    setEnabled(target, enable) {
        if (enable) {
            target.removeAttribute('disabled');
        } else {
            target.setAttribute('disabled', 'disabled')
        }
    }

    setLoading(target, enable) {
        if (enable) {
            target.setAttribute('loading', 'loading')
        } else {
            target.removeAttribute('loading');
        }
    }

    setActive(target, enable) {
        if (enable) {
            target.setAttribute('active', 'active');
        } else {
            target.removeAttribute('active');
        }
    }

    initStatus() {
        this.statusTargets.forEach((s) => {
            let _call = (target) => {
                this.setActive(target, false);
                this.setLoading(target, true);

                axios.get(target.getAttribute('data-check-url')).then((response) => {
                    this.setLoading(target, false);
                    this.setActive(target, response.data.success);
                    target.innerText = response.data.message;

                    setTimeout(() => {
                        _call(target);
                    }, 30000);

                }).catch((err) => {
                    console.error(err);
                    setTimeout(() => _call(target), 30000);
                });
            };
            _call(s);
        });
    }
}
