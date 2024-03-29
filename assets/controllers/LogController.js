import { Controller } from '@hotwired/stimulus';
import axios from "axios";

export default class LogController extends Controller {
    static targets = [
        'console',
    ]
    static values = {
        url: String,
        interval: Number
    }

    connect() {
        this.initLogging();
    }

    initLogging() {
        this._fetch(() => this._run());
    }

    _run() {
        setTimeout(()=>{
            this._fetch(() => this._run());
        }, this.intervalValue * 1000);
    }
    _fetch(success) {
        try {
            axios.get(this.urlValue).then((response) => {
                this.consoleTarget.innerHTML = response.data.log;
                this.consoleTarget.scroll(0, this.consoleTarget.scrollHeight);
                success();
            }).catch((err) => {
                console.error(err);
                success();
            });
        } catch (e) {console.error(e);}
    }
}
