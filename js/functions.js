// Copy
if (document.querySelector('#shortCodeBox')) {
    document.querySelector('#shortCodeBox').onclick = e => {
        let tempInput = document.createElement('input');

        document.querySelector('body').appendChild(tempInput);
        tempInput.setAttribute('value', e.target.innerText);
        tempInput.select();
        document.execCommand('copy');
        document.querySelector('body').removeChild(tempInput);

        document.querySelector('#shortCodeAlert').style.display = 'block';
        setTimeout(() => {
            document.querySelector('#shortCodeAlert').style.display = 'none';
        }, 2000);
    };
}