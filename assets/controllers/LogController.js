import { Controller } from '@hotwired/stimulus';
import axios from "axios";

export default class LogController extends Controller {
    static targets = [
        'console',
    ]

    connect() {
        this.initLogging();
    }

    initLogging() {
        this._fetch(() => this._run());
    }

    _run() {
        setTimeout(()=>{
            this._fetch(() => this._run());
        }, 15000);
    }
    _fetch(success) {
        axios.get('/dashboard/pv-log').then((response) => {
            this.consoleTarget.innerText = response.data.log;
            this.consoleTarget.scroll(0, this.consoleTarget.scrollHeight);
            success();
        });

    }
}
