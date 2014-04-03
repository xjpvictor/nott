if (!window.location.origin) {
  window.location.origin = window.location.protocol+'//'+window.location.hostname+(window.location.port?':'+window.location.port:'');
}
function getSelectionHtml() {
  var html = '';
  if (typeof window.getSelection != 'undefined') {
    var sel = window.getSelection();
    if (sel.rangeCount) {
      var container = document.createElement('div');
      for (var i = 0, len = sel.rangeCount; i<len; ++i) {
        container.appendChild(sel.getRangeAt(i).cloneContents());
      }
      html = container.innerHTML;
    }
  } else if (typeof document.selection != 'undefined') {
    if (document.selection.type == 'Text') {
      html = document.selection.createRange().htmlText;
    }
  }
  function removeAttr(str) {
    var s = ['id','class','on[^=]+','data','dynsrc','accesskey','tabindex','jsaction','align','border'];
    for (var i = 0; i < s.length; i++) {
      str = str.replace(new RegExp('<([^>]* +)?'+s[i]+' *= *(("[^"]*")|(\'[^\']*\'))?( +[^>]*)?(\\/)?>','gi'),'<$1$5$6>');
    }
    return str;
  }
  return html ? removeAttr(('\n'+html).replace(/<\/?(style|script|input|select|option|textarea|audio|video|source|form|object|embed|iframe|frame|frameset|label|meta|noscript|xml|applet|bgsound|fieldset|button|link|legend|b|i|u)( +[^>]*)?>/gi,'')).replace(/<([^>]* +)(href|src) *= *("|\')\//gi,'<$1$2=$3'+window.location.origin+'/').replace(/<a +href *= *("|\')#/gi,'<a href=$1'+document.location.href+'#').replace(/<\/?p( [^>]*)?>/gi,'\n\n').replace(/[\r\n]+(\r|\n| |&nbsp;)*[\r\n]+/gi,'\n\n').replace(/[\r\n]+( |&nbsp;){4,}/gi,'\n\n').replace(/([\r\n]+(\d+))\.(\s+)/g,'$1\\.$3').replace(/([\r\n]+)((>|#)+)/g,'$1\\$2').replace(/<([^> ]+)(\s+[^>]*)?\s+(class|id)\s*=\s*("|\')[^"\']*("|\')(\s+[^>]*)?(\/?)>/i,'<$1$2$6$7>').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/^[\r\n]*/,'').replace(/[\r\n]{2,}$/,'') : '';
};

if (!document.getElementById('nott_iframe')) {
  var text = getSelectionHtml();
  var iframe = document.createElement('iframe');
  iframe.frameBorder = 0;
  iframe.width = '500px';
  iframe.height = '490px';
  iframe.id = 'nott_iframe';
  iframe.src = url + 'frame.php?url=' + encodeURIComponent(window.location.origin) + '&href=' + encodeURIComponent(document.location.href);
  iframe.style.position = 'fixed';
  iframe.style.right = '10px';
  iframe.style.top = '10px';
  iframe.style.zIndex = 100000;
  iframe.style.border = 'none';
  iframe.onload = function() {
    iframe.contentWindow.postMessage(text, url);
  }
  document.body.appendChild(iframe);

  window.addEventListener('message', function(e) {
    if (e.data == 'nott_close' && e.origin+'/' == url) {
      document.body.removeChild(document.getElementById('nott_iframe'));
    }
  });
}
