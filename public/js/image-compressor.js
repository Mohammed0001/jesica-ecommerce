/**
 * Client-side image compressor for admin upload forms.
 *
 * Resizes large images in the browser BEFORE uploading, drastically
 * cutting upload time. Uses the Canvas API — no dependencies.
 *
 * Every wait here is bounded. FileReader, Image decoding and canvas.toBlob
 * can all fail by simply never invoking their callback — common on iOS
 * Safari and low-memory Android with large photos. An unbounded Promise.all
 * over those never settles, so neither .then() nor .catch() runs, the form
 * is never submitted, and the page sits on the overlay forever with no
 * error. Anything that overruns its deadline falls back to the original
 * file and the form still submits.
 */
(function () {
    'use strict';

    const MAX_WIDTH  = 2000;
    const MAX_HEIGHT = 2000;
    const QUALITY    = 0.80;   // JPEG quality 0-1
    const OUTPUT_TYPE = 'image/jpeg';

    const PER_FILE_TIMEOUT = 20000;  // one photo
    const TOTAL_TIMEOUT    = 45000;  // the whole compression phase

    /**
     * Resolve with `fallback` if `promise` has not settled within `ms`.
     */
    function withTimeout(promise, ms, fallback) {
        return new Promise(function (resolve) {
            var settled = false;

            var timer = setTimeout(function () {
                if (!settled) {
                    settled = true;
                    resolve(fallback);
                }
            }, ms);

            promise.then(function (value) {
                if (!settled) {
                    settled = true;
                    clearTimeout(timer);
                    resolve(value);
                }
            }, function () {
                if (!settled) {
                    settled = true;
                    clearTimeout(timer);
                    resolve(fallback);
                }
            });
        });
    }

    /**
     * Compress a single File via canvas and return a new File.
     */
    function compressImage(file) {
        var work = new Promise(function (resolve) {
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
                    if (!ctx || typeof canvas.toBlob !== 'function') {
                        return resolve(file);
                    }

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

        // A photo that cannot be re-encoded is uploaded as-is rather than
        // stalling the submit.
        return withTimeout(work, PER_FILE_TIMEOUT, file);
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

            var submitted = false;
            var submitOnce = function () {
                if (submitted) return;
                submitted = true;

                form.dataset.compressed = 'true';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            };

            var imageInputs = Array.from(
                form.querySelectorAll('input[type="file"][accept*="image"]')
            );

            // Compress all image file inputs in parallel, but never let the
            // submit itself depend on that finishing.
            withTimeout(
                Promise.all(imageInputs.map(compressInputFiles)),
                TOTAL_TIMEOUT,
                null
            ).then(submitOnce, submitOnce);
        });
    });
})();
