if (document.getElementById('edit-title')) {
  var textarea = document.getElementById('post-d');
  var edittitle = document.getElementById('edit-title').innerHTML;
  textarea.onkeydown = function(e) {
    var s = this.selectionStart;
    if (e.keyCode == 9 || event.which == 9) {
      e.preventDefault();
      this.value = this.value.substring(0, s) + '    ' + this.value.substring(this.selectionEnd);
      this.selectionEnd = s+4;
    } else if (e.keyCode == 13 || event.which == 13) {
      var regexp = new RegExp('^((    )|(> )|(- )|(([0-9]+). ))');
      if ((str = this.value.substring(this.value.substring(0, s).lastIndexOf("\n")+1).match(regexp)) !== null) {
        e.preventDefault();
        if (typeof str[6] !== 'undefined') {
          str = parseInt(str[6])+1+'. ';
        } else {
          str = str[1];
        }
        this.value = this.value.substring(0, s) + "\n" + str + this.value.substring(this.selectionEnd);
        this.selectionEnd = s+str.length+1;
      }
    }
  }
  function mdTitle(str) {
    if (str.length) {
      document.getElementById('edit-title').innerHTML = str;
    } else {
      document.getElementById('edit-title').innerHTML = edittitle;
    }
  }
  function mdFocus(a,d) {
    textarea.setSelectionRange(a,d);
    textarea.focus();
  }
  function mdAddHead(str) {
    var a = textarea.selectionStart;
    var d = textarea.selectionEnd;
    var p = textarea.value.substring(0,a).lastIndexOf('\n')+1;
    if (textarea.value.substr(p, str.length) == str) {
      textarea.value = textarea.value.substring(0,p) + textarea.value.substring(p+str.length);
      a = a-str.length;
      d = d-str.length;
    } else {
      textarea.value = textarea.value.substring(0,p) + str + textarea.value.substring(p);
      a = a+str.length;
      d = d+str.length;
    }
    mdFocus(a,d);
  }
  function mdAddStyle(str) {
    var a = textarea.selectionStart;
    var d = textarea.selectionEnd;
    if (textarea.value.substr(a-str.length, str.length) == str) {
      textarea.value = textarea.value.substring(0,a-str.length) + textarea.value.substring(a,d) + textarea.value.substring(d+str.length);
      a = a-str.length;
      d = d-str.length;
    } else {
      textarea.value = textarea.value.substring(0,a) + str + textarea.value.substring(a,d) + str + textarea.value.substring(d);
      a = a+str.length;
      d = d+str.length;
    }
    mdFocus(a,d);
  }
  function mdAddURL() {
    var a = textarea.selectionStart;
    var d = textarea.selectionEnd;
    var regexp = new RegExp('\\[([^\\]]*)\\]\\([^\\)]*\\)');
    if ((str = textarea.value.substr(a,d+1).match(regexp)) !== null) {
      str = str[1];
      if (str == 'text') {
        str = '';
      }
      textarea.value = textarea.value.substring(0,a) + str + textarea.value.substring(d);
      a = a+str.length;
      d = a;
    } else {
      if (d > a) {
        textarea.value = textarea.value.substring(0,a) + '[' + textarea.value.substring(a,d) + '](url "' + textarea.value.substring(a,d) + '")' + textarea.value.substring(d);
        a = d+3;
        d = a+3;
      } else {
        textarea.value = textarea.value.substring(0,a) + '[text](url "title")' + textarea.value.substring(d);
        a = a+1;
        d = a+4;
      }
    }
    mdFocus(a,d);
  }
  function mdAddHR(str) {
    var a = textarea.selectionStart;
    var d = textarea.selectionEnd;
    var p = textarea.value.substring(d).indexOf("\n")+d+1;
    if (str == '***') {
      textarea.value = textarea.value.substring(0,p) + "\n" + str + "\n\n" + textarea.value.substring(p);
      a = p+6;
      d = a;
    } else if (str.indexOf('!') == '0') {
      textarea.value = textarea.value.substring(0,p) + "\n" + str + "\n\n" + textarea.value.substring(p);
      a = p+str.length+3;
      d = a;
    } else {
      textarea.value = textarea.value.substring(0,p) + str + "\n\n" + textarea.value.substring(p);
    }
    mdFocus(a,d);
  }
}
if (window.File && window.FileList && window.FileReader && window.XMLHttpRequest && document.getElementById('upload-list')) {
  var out = true;
  var timeout = -1;
  var upload = new Array();
  var upl = document.getElementById('upload-list');
  var xhr = new XMLHttpRequest();
  uploadAddClass('upload-drop-text', 'show');
  uploadDnd();
}
function uploadDnd() {
  if (document.getElementById("upload-file-button-wrap")) {
    document.getElementById("upload-file-button-wrap").innerHTML=document.getElementById("upload-file-button-wrap").innerHTML;
    document.getElementById("upload-file-button").addEventListener("change", uploadFileSelectHandler, false);
    document.getElementById("upload-drop").addEventListener("drop", uploadFileSelectHandler, false);
    var filedrop = document.getElementsByTagName("body")[0];
    filedrop.addEventListener("dragover", uploadFileDragHover, false);
    filedrop.addEventListener("dragleave", uploadFileDragHover, false);
    filedrop.addEventListener("drop", uploadCancelDrag, false);
  }
}
function uploadFileDragHover(e) {
  e.stopPropagation();
  e.preventDefault();
  if (e.type == "dragover") {
    out = false;
    uploadAddClass("upload-drop", "upload-drag");
  } else if (e.type == "dragleave") {
    out = true;
    clearTimeout(timeout);
    timeout = setTimeout(function() {
      if (out) {
        uploadRemoveClass("upload-drop", "upload-drag");
      }
    }, 100);
  } else {
    uploadRemoveClass("upload-drop", "upload-drag");
  }
}
function uploadCancelDrag(e) {
  e.stopPropagation();
  e.preventDefault();
  uploadRemoveClass("upload-drop", "upload-drag");
}
function uploadFileSelectHandler(e) {
  uploadFileDragHover(e);
  var files = e.target.files || e.dataTransfer.files;
  if (files.length) {
    uploadShowlist(files);
    uploadUploadFile();
  }
}
function uploadUploadFile() {
  var list = upl.children;
  if (list.length) {
    var item = list[0];
    var id = item.getAttribute('data-id');
    if (typeof upload['_'+id] !== 'undefined') {
      var f = upload['_'+id];
      var name = f.name;
      var url = base_url + '&name='+id+'-'+encodeURIComponent(name);
      xhr.open("POST", url, true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
          if (xhr.status == 200) {
            document.getElementById('attachment-list').innerHTML = xhr.responseText + document.getElementById('attachment-list').innerHTML;
          }
          if (document.getElementById('upload-file-'+id)) {
            item.parentNode.removeChild(item);
          }
          uploadUploadFile();
        }
      }
      xhr.upload.addEventListener('progress', function(e) {
        if (document.getElementById('upload-progress-'+id)) {
          if (e.lengthComputable) {
            var uploaded = e.loaded;
            var total = e.total;
            var percentage = Math.round((e.loaded / e.total) * 100);
            document.getElementById('upload-progress-'+id).style.width = percentage + '%';
          }
        }
      }, false);
      document.getElementById('upload-cancel-'+id).addEventListener('click', function() {
        xhr.abort();
        uploadUploadFile();
      }, false);
      var reader = new FileReader();
      reader.onload = function() {
        xhr.send("file="+reader.result);
      }
      reader.readAsDataURL(f);
    }
  } else {
    uploadClear();
  }
}
function uploadShowlist(files) {
  uploadAddClass('upload-clear', 'show');
  uploadAddClass('upload-button', 'half');
  uploadAddClass('upload-file-button-wrap', 'half');
  var t = Math.round(+new Date()/1000);
  for (var i=0;i<files.length;i++) {
   upl.innerHTML += '<div id="upload-file-'+t+'-'+i+'" class="upload-file show" data-id="'+t+'-'+i+'">'+uploadStringHtmlentities(files[i].name)+'<span class="delete" id="upload-cancel-'+t+'-'+i+'" onclick="this.parentNode.parentNode.removeChild(this.parentNode);">&#10007;</span><div class="upload-progress" id="upload-progress-'+t+'-'+i+'"></div></div>';
   upload['_'+t+'-'+i] = files[i];
  }
}
function uploadClear() {
  uploadRemoveClass("upload-clear", "show");
  uploadRemoveClass("upload-button", "half");
  uploadRemoveClass("upload-file-button-wrap", "half");
  document.getElementById("upload-list").innerHTML = "";
  if (window.File && window.FileList && window.FileReader && window.XMLHttpRequest) {
    xhr.abort();
    uploadDnd();
  } else {
    document.getElementById("upload-file-button-wrap").innerHTML=document.getElementById("upload-file-button-wrap").innerHTML;
  }
}
function uploadAddClass(id, cls) {
  if (document.getElementById(id)) {
    document.getElementById(id).classList.add(cls);
  }
}
function uploadRemoveClass(id, cls) {
  if (document.getElementById(id)) {
    document.getElementById(id).classList.remove(cls);
  }
}
function uploadStringHtmlentities(str) {
  var h = document.getElementById("uploadhtmlentities");
  h.appendChild(document.createTextNode(str));
  var fn = h.innerHTML.replace(/\"/g, "&quot;").replace(/\'/g, "&#39;");
  h.innerHTML = "";
  return fn;
}
function deleteAttachment(id, name, elem) {
  if (confirm('Permanently delete this file?')) {
    var x=document.createElement('SCRIPT');
    x.type='text/javascript';
    x.src='attachment.php?id='+id+'&name='+name+'&elem='+elem+'&action=delete';
    document.getElementsByTagName('head')[0].appendChild(x);
  }
}
function autoSave(s) {
  if (typeof s !== "undefined") {
    var http = new XMLHttpRequest();
    http.open("POST", 'clipboard.php', true);
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    http.withCredentials = true;
    http.onreadystatechange = function() {
      if (http.readyState == 4 && http.status == 200) {
        str = document.getElementById('submit').value;
        document.getElementById('submit').value = 'Saved';
        document.getElementById('submit').disabled = true;
        setTimeout("document.getElementById('submit').value=str;document.getElementById('submit').disabled=false;", 1500);
      }
    }
    http.send("d="+encodeURIComponent(s));
  }
}
function pasteImage(event) {
  var items = (event.clipboardData || event.originalEvent.clipboardData).items;
  if (typeof items !== "undefined" && items.length) {
    var blob = items[0].getAsFile();
    if (blob) {
      var reader = new FileReader();
      reader.onload = function(event){
        var t = Math.round(+new Date()/1000);
        var url = base_url + '&name=' + t + '-0-' + 'clipboard-' + t;
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
          if (xhr.readyState == 4) {
            if (xhr.status == 200) {
              document.getElementById('attachment-list').innerHTML = xhr.responseText + document.getElementById('attachment-list').innerHTML;
              //document.getElementById('post-d').innerHTML = document.getElementById('attachment-list').innerHTML;
            }
          }
        }
        xhr.send("file="+event.target.result);
      }
      reader.readAsDataURL(blob);
    }
  }
}
