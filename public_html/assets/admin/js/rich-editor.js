(function () {
    'use strict';

    function csrfToken() {
        var tag = document.querySelector('meta[name="csrf-token"]');
        return tag ? tag.getAttribute('content') : '';
    }

    function uploadRichTextFile(file, type, progress) {
        var formData = new FormData();
        formData.append('file', file);
        formData.append('type', type || (file.type && file.type.indexOf('image/') === 0 ? 'image' : 'file'));

        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', window.adminRichTextUploadUrl || '/admin/rich-text/upload');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken());
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.onprogress = function (event) {
                if (event.lengthComputable && typeof progress === 'function') {
                    progress(event.loaded / event.total * 100);
                }
            };

            xhr.onload = function () {
                var json;

                if (xhr.status < 200 || xhr.status >= 300) {
                    reject('آپلود فایل با خطا مواجه شد.');
                    return;
                }

                try {
                    json = JSON.parse(xhr.responseText);
                } catch (error) {
                    reject('پاسخ آپلود معتبر نیست.');
                    return;
                }

                if (!json || typeof json.location !== 'string') {
                    reject('آدرس فایل آپلود شده دریافت نشد.');
                    return;
                }

                resolve(json.location);
            };

            xhr.onerror = function () {
                reject('ارتباط با سرور آپلود برقرار نشد.');
            };

            xhr.send(formData);
        });
    }


    function escapeHtml(value) {
        return String(value || '').replace(/[&<>\"]/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '\"': '&quot;' })[char];
        });
    }

    function openMediaLibrary(callback) {
        var url = window.adminMediaPickerUrl || '/admin/media-picker';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.json(); })
            .then(function (json) {
                var items = (json && json.items) || [];
                if (!items.length) {
                    window.alert('تصویری در کتابخانه رسانه یافت نشد.');
                    return;
                }

                var overlay = document.createElement('div');
                overlay.style.cssText = 'position:fixed;inset:0;z-index:100000;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;padding:24px;';
                var panel = document.createElement('div');
                panel.style.cssText = 'background:#fff;border-radius:16px;max-width:900px;width:100%;max-height:80vh;overflow:auto;padding:18px;direction:rtl;box-shadow:0 24px 80px rgba(15,23,42,.24);';
                panel.innerHTML = '<div style="display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:14px"><strong>انتخاب تصویر از کتابخانه رسانه</strong><button type="button" data-close style="border:0;background:#e2e8f0;border-radius:10px;padding:8px 12px;cursor:pointer">بستن</button></div>';
                var grid = document.createElement('div');
                grid.style.cssText = 'display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;';

                items.forEach(function (item) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.style.cssText = 'border:1px solid #e2e8f0;background:#fff;border-radius:12px;padding:8px;text-align:right;cursor:pointer;';
                    button.innerHTML = '<img src="' + item.url + '" alt="" style="width:100%;height:95px;object-fit:cover;border-radius:10px;margin-bottom:8px"><span style="display:block;font-size:12px;color:#334155;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + escapeHtml(item.title || 'تصویر') + '</span>';
                    button.addEventListener('click', function () {
                        document.body.removeChild(overlay);
                        callback(item.url, { title: item.title || '', alt: item.alt || item.title || '' });
                    });
                    grid.appendChild(button);
                });

                panel.appendChild(grid);
                overlay.appendChild(panel);
                overlay.addEventListener('click', function (event) {
                    if (event.target === overlay || event.target.hasAttribute('data-close')) {
                        document.body.removeChild(overlay);
                    }
                });
                document.body.appendChild(overlay);
            })
            .catch(function () { window.alert('دریافت کتابخانه رسانه با خطا مواجه شد.'); });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.tinymce || !document.querySelector('textarea.js-rich-editor')) {
            return;
        }

        window.tinymce.init({
            selector: 'textarea.js-rich-editor',
            directionality: 'rtl',
            height: 360,
            menubar: false,
            branding: false,
            promotion: false,
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            plugins: 'advlist autolink link image lists charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table wordcount directionality',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignright aligncenter alignleft alignjustify | bullist numlist outdent indent | link image media table | ltr rtl | removeformat code fullscreen',
            valid_elements: 'p,br,strong/b,em/i,u,s,ul,ol,li,a[href|target|rel|title],img[src|alt|title|width|height],blockquote,pre,code,h1,h2,h3,h4,h5,h6,hr,table,thead,tbody,tfoot,tr,th[colspan|rowspan],td[colspan|rowspan],figure,figcaption,span[class],div[class],sub,sup',
            extended_valid_elements: 'a[href|target|rel|title],img[src|alt|title|width|height]',
            link_default_target: '_blank',
            link_rel_list: [
                { title: 'بدون مقدار', value: '' },
                { title: 'noopener noreferrer', value: 'noopener noreferrer' }
            ],
            images_upload_handler: function (blobInfo, progress) {
                return uploadRichTextFile(blobInfo.blob(), 'image', progress);
            },
            file_picker_types: 'image file media',
            file_picker_callback: function (callback, value, meta) {
                if (meta.filetype === 'image') {
                    openMediaLibrary(callback);
                    return;
                }

                var input = document.createElement('input');
                input.type = 'file';
                input.accept = meta.filetype === 'image' ? 'image/*' : '.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt,image/*';

                input.onchange = function () {
                    var file = input.files && input.files[0];
                    if (!file) {
                        return;
                    }

                    uploadRichTextFile(file, meta.filetype === 'image' ? 'image' : 'file').then(function (url) {
                        callback(url, { title: file.name, text: file.name, alt: file.name });
                    }).catch(function (message) {
                        window.alert(message);
                    });
                };

                input.click();
            },
            setup: function (editor) {
                var syncEditor = function () {
                    editor.save();
                };

                editor.on('change keyup input undo redo setcontent ExecCommand', syncEditor);

                editor.on('init', function () {
                    var textarea = editor.getElement();
                    var form = textarea && textarea.form;

                    syncEditor();

                    if (form && form.dataset.richEditorSubmitSync !== 'true') {
                        form.dataset.richEditorSubmitSync = 'true';

                        var syncAllEditors = function () {
                            if (window.tinymce) {
                                window.tinymce.triggerSave();
                            }
                        };

                        form.addEventListener('submit', syncAllEditors, true);
                        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
                            button.addEventListener('click', syncAllEditors, true);
                            button.addEventListener('mousedown', syncAllEditors, true);
                            button.addEventListener('touchstart', syncAllEditors, true);
                        });
                    }
                });
            }
        });
    });
})();
