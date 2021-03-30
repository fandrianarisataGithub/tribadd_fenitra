function cropImage(container, origin) {
    const canvas = $('canvas')[0];
    const ctx = canvas.getContext('2d');
    let img;
    function isDataURL(s) {
        return !!s.match(isDataURL.regex);
    }
    isDataURL.regex = /^\s*data:([a-z]+\/[a-z]+(;[a-z\-]+\=[a-z\-]+)?)?(;base64)?,[a-z0-9\!\$\&\'\,\(\)\*\+\,\;\=\-\.\_\~\:\@\/\?\%\s]*\s*$/i;

    if (isDataURL(origin.getAttribute('src')) || !!origin.getAttribute('src').match(/assets\/image\/user\.png/i)) {
        $(origin).removeClass('d-none')
        return false
    }

    load(origin.getAttribute('src'))

    function load(src) {
        $(origin).addClass('d-none')
        $(container).find('div.water').removeClass('d-none')
        img = new Image();
        img.onload = function() {
            run();
        };
        img.src = src;
    }

    function run() {
        if (!img) return;
        const options = {
            width: container.offsetWidth,
            height: container.offsetHeight,
            minScale: 1,
            ruleOfThirds: true,
            debug: true
        };
        const analyzeOptions = analyze.bind(this, options);
        // faceDetectionJquery(options, analyzeOptions);
        analyzeOptions();
    }

    /*function faceDetectionJquery(options, callback) {
        $(img).faceDetection({
            complete: function(faces) {
                if (faces === false) {
                    return
                }
                options.boost = Array.prototype.slice
                    .call(faces, 0)
                    .map(function(face) {
                        return {
                            x: face.x,
                            y: face.y,
                            width: face.width,
                            height: face.height,
                            weight: 1.0
                        };
                    });

                callback(options);
            }
        });
    }*/

    function analyze(options) {
        smartcrop.crop(img, options, draw);
    }

    function draw(result) {
        const selectedCrop = result.topCrop;
        drawCrop(selectedCrop);
    }

    function drawCrop(crop) {
        canvas.width = container.offsetWidth;
        canvas.height = container.offsetHeight;
        ctx.drawImage(
            img,
            crop.x,
            crop.y,
            crop.width,
            crop.height,
            0,
            0,
            canvas.width,
            canvas.height
        );
        $(origin).attr('src', canvas.toDataURL(img.type))
        $(origin).removeClass('d-none')
        $(container).find('div.water').addClass('d-none')
    }
}