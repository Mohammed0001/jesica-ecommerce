/**
 * Client-side image compressor for admin upload forms.
 *
 * Resizes large images in the browser BEFORE uploading, drastically
 * cutting upload time. Uses the Canvas API — no dependencies.
 */
(function () {
    'use strict';

    const MAX_WIDTH  = 2000;
    const MAX_HEIGHT = 2000;
    const QUALITY    = 0.80;   // JPEG quality 0-1
    const OUTPUT_TYPE = 'image/jpeg';

    /**
     * Compress a single File via canvas and return a new File.
     */
    function compressImage(file) {
        return new Promise(function (resolve) {
            // Skip non-image files (e.g. PDFs)
            if (!file.type.startsWith('image/')) {
                return resolve(file);
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                var img = new Image();
                img.onload = function () {
                    var width  = img.width;
                    var height = img.height;

                    // Calculate new dimensions
                    if (width > MAX_WIDTH || height > MAX_HEIGHT) {
                        var ratio = Math.min(MAX_WIDTH / width, MAX_HEIGHT / height);
                        width  = Math.round(width  * ratio);
                        height = Math.round(height * ratio);
                    }

                    var canvas  = document.createElement('canvas');
                    canvas.width  = width;
                    canvas.height = height;

                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function (blob) {
                        if (!blob) return resolve(file);

                        // Only use compressed version if it's actually smaller
                        if (blob.size >= file.size) {
                            return resolve(file);
                        }

                        var compressedFile = new File(
                            [blob],
                            file.name.replace(/\.[^.]+$/, '.jpg'),
                            { type: OUTPUT_TYPE, lastModified: Date.now() }
                        );
                        resolve(compressedFile);
                    }, OUTPUT_TYPE, QUALITY);
                };
                img.onerror = function () { resolve(file); };
                img.src = e.target.result;
            };
            reader.onerror = function () { resolve(file); };
            reader.readAsDataURL(file);
        });
    }

    /**
     * Replace the files in an <input type="file"> with compressed versions
     * by swapping them into a DataTransfer object.
     */
    function compressInputFiles(input) {
        var files = Array.from(input.files);
        if (files.length === 0) return Promise.resolve();

        return Promise.all(files.map(compressImage)).then(function (compressed) {
            var dt = new DataTransfer();
            compressed.forEach(function (f) { dt.items.add(f); });
            input.files = dt.files;
        });
    }

    /**
     * Intercept form submission: compress all image inputs, then submit.
     */
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('form[enctype="multipart/form-data"]');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            // Prevent default only if we haven't already compressed
            if (form.dataset.compressed === 'true') return;
            e.preventDefault();

            var imageInputs = Array.from(
                form.querySelectorAll('input[type="file"][accept*="image"]')
            );

            // Compress all image file inputs in parallel
            Promise.all(imageInputs.map(compressInputFiles))
                .then(function () {
                    form.dataset.compressed = 'true';
                    form.requestSubmit();
                })
                .catch(function () {
                    // On error, submit with original files
                    form.dataset.compressed = 'true';
                    form.requestSubmit();
                });
        });
    });
})();
