"use strict";

// Shared client-side image downscaler used by any multipart form with photo
// uploads (product add/edit, admin vendor create, etc.). Oversized camera
// photos uploaded at full resolution add real seconds to both the upload
// itself and the server-side re-encode — resizing in the browser first cuts
// both costs since the source pixels are already smaller.
const SHARED_IMAGE_MAX_DIMENSION = 1600;

function resizeImageFile(file, maxDim) {
    return new Promise((resolve) => {
        if (!file.type || !file.type.startsWith('image/') || file.type === 'image/gif') {
            resolve(file);
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        const img = new Image();
        img.onload = function () {
            const { width, height } = img;
            if (width <= maxDim && height <= maxDim) {
                URL.revokeObjectURL(objectUrl);
                resolve(file);
                return;
            }

            const scale = Math.min(maxDim / width, maxDim / height);
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(width * scale);
            canvas.height = Math.round(height * scale);
            canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
            URL.revokeObjectURL(objectUrl);

            const outputType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
            canvas.toBlob((blob) => {
                if (!blob) {
                    resolve(file);
                    return;
                }
                resolve(new File([blob], file.name, { type: outputType, lastModified: Date.now() }));
            }, outputType, 0.85);
        };
        img.onerror = function () {
            URL.revokeObjectURL(objectUrl);
            resolve(file);
        };
        img.src = objectUrl;
    });
}

// Walks every entry in a FormData, resizing any oversized image files in
// place. Field names are untouched (including repeated array-style keys), so
// the server never sees a difference beyond file size.
function resizeFormDataImages(formData, maxDim) {
    maxDim = maxDim || SHARED_IMAGE_MAX_DIMENSION;
    const entries = Array.from(formData.entries());
    const fileEntries = entries.filter(([, value]) => value instanceof File && value.size > 0);

    return Promise.all(fileEntries.map(([, file]) => resizeImageFile(file, maxDim)))
        .then((resizedFiles) => {
            // Rebuild fresh rather than mutate formData in place — FormData has no
            // "replace value at this position" operation, only delete-all-by-key
            // then re-append, which would reorder repeated keys.
            let fileIdx = 0;
            const rebuilt = new FormData();
            entries.forEach(([key, value]) => {
                if (value instanceof File && value.size > 0) {
                    rebuilt.append(key, resizedFiles[fileIdx]);
                    fileIdx++;
                } else {
                    rebuilt.append(key, value);
                }
            });
            return rebuilt;
        });
}
