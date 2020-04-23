// Copy
document.querySelector('#shortCodeBox').onclick = e => {
    document.querySelector('#shortCodeBoxInput').select();
    document.execCommand('copy');
    document.querySelector('#shortCodeAlert').style.display = 'block';
    setTimeout(() => {
        document.querySelector('#shortCodeAlert').style.display = 'none';
    }, 2000)
};