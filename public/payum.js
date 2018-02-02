/**
 * @callback requestCallback
 * @param {{targetUrl}} token
 */

class Payum {

    /**
     * @param {string} serverUrl
     */
    constructor(serverUrl) {
        this.serverUrl = serverUrl;

        this.payment = {
            create: this.createPayment.bind(this),
            get: this.getPayment.bind(this)
        };

        this.token = {
            create: this.createToken.bind(this)
        };
    }

    /**
     * @param {string} payment
     * @param {requestCallback} callback
     */
    createPayment(payment, callback) {
        fetch(this.serverUrl + '/payments', {
            body: JSON.stringify(payment),
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
            .then(res => res.json())
            .then((data) => {
                callback(data.payment);
            });
    }

    /**
     * @param {int} id
     * @param {requestCallback} callback
     */
    getPayment(id, callback) {
        fetch(this.serverUrl + '/payments/' + id)
            .then(res => res.json())
            .then(
                (data) => callback(data.payment)
            );
    }

    /**
     * @param {string} token
     * @param {requestCallback} callback
     */
    createToken(token, callback) {
        fetch(this.serverUrl + '/tokens', {
            body: JSON.stringify(token),
            method: 'POST',
            headers: {
                'Accept': 'application/vnd.payum+json',
                'Content-Type': 'application/json',
            }
        })
            .then(res => res.json())
            .then(
                (data) => callback(data.token)
            );
    }

    /**
     * @param {string} url
     * @param {string} container
     */
    execute(url, container) {
        fetch(url, {
            headers: {
                'Accept': 'application/vnd.payum+json',
            }
        })
            .then(res => res.json())
            .then(
                (data) => {
                    Payum.updateContainer(data, container);
                    this.bindSubmitForm(container);
                },
                () => Payum.updateContainer({status: 500}, container)
            );
    }

    /**
     * @param {string} container
     */
    bindSubmitForm(container) {
        const form = document.querySelector(container + ' form');

        if (!form) {
            return;
        }

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const gateway = formData.entries().next().value;
            const values = `${gateway[0]}=${gateway[1]}`;

            fetch(form.action, {
                body: values,
                method: 'POST',
                headers: {
                    'Accept': 'application/vnd.payum+json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                }
            })
                .then(res => res.json())
                .then(
                    (data) => Payum.updateContainer(data, container),
                    () => Payum.updateContainer({status: 500}, container)
                );
        });
    }

    /**
     * @param {{content?,status,headers?:{Location},responseJSON}} data
     * @param {string} container
     */
    static updateContainer(data, container) {
        // For php exceptions, which not return status
        if (!data.status) {
            data.status = 500;
        }

        if (data.status === 302) {
            window.location.replace(data.headers.Location);
        }

        if (data.status >= 200 && data.status < 300) {
            Payum.insertHtml(container, data.content);
        }

        if (data.status >= 400 && data.status < 500) {
            Payum.insertHtml(container, '<div class="alert alert-warning">Bad request error</div>');
        }

        if (data.status >= 500 && data.status < 600) {
            Payum.insertHtml(container, '<div class="alert alert-warning">Internal server error</div>');
        }
    }

    /**
     * @param {string} container
     * @param {string} html
     */
    static insertHtml(container, html) {
        const containerElement = document.querySelector(container);

        containerElement.innerHTML = html;

        Payum.loadAndExeCuteJavascript(container);
    }

    /**
     * @param {string} container
     */
    static loadAndExeCuteJavascript(container) {
        const scripts = document.querySelectorAll(container + ' script');
        let scriptsLoaded = 0;
        let scriptsCount = scripts.length;
        let scriptsToExecute = [];

        for (let script of scripts) {
            const scriptParent = script.parentNode;
            const newScript = document.createElement('script');

            for (let attribute of script.attributes) {
                newScript.setAttribute(attribute.nodeName, attribute.nodeValue);
            }

            for (let attributeName in script.dataset) {
                newScript.dataset[attributeName] = script.dataset[attributeName];
            }

            if (script.src) {
                script.remove();
                scriptParent.appendChild(newScript);

                newScript.addEventListener('load', () => {
                    scriptsLoaded++;

                    if (scriptsCount === scriptsLoaded) {
                        Payum.executeInlineJavascript(scriptsToExecute);
                    }
                });
            } else {
                scriptsCount--;

                const code = script.textContent;
                script.remove();

                newScript.type = 'text/javascript';
                newScript.appendChild(document.createTextNode(code));

                scriptsToExecute.push({
                    parentNode: scriptParent,
                    scriptNode: newScript
                });
            }
        }

        if (scriptsCount === 0) {
            Payum.executeInlineJavascript(scriptsToExecute);
        }
    }

    /**
     * @param {string[]} scriptsToExecute
     */
    static executeInlineJavascript(scriptsToExecute) {
        for (let code of scriptsToExecute) {
            code.parentNode.appendChild(code.scriptNode);
        }

    }
}
