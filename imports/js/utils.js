function el(a) { return document.getElementById(a); }

function _open(id) {
    el(id).style.display = 'block';
}

function _close(id) {
    el(id).style.display = 'none';
}

function jsonPost(url, obj, fun) {
    let xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            let obj = JSON.parse(this.responseText);
            if (obj.a === 0) {
                alert('An error has been detected. Please try again.');
                return;
            }
            fun(obj);
        }
    };

    let par = 't=' + Math.random();
    par += '&json=' + JSON.stringify(obj);
    xmlhttp.open('POST', url, true);
    xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xmlhttp.send(par);
}