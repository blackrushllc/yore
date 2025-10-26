// Script
function jclock(datetime) {
    var o = calcTime(datetime);

    var output = '';

    if (o.expired) {
        output += '<div class="clocky">';
        output += "EXPIRED<br/>";
        output += "" + o.days + "D&nbsp;";
        output += "" + o.hours + "H&nbsp;";
        output += ": " + o.minutes + "M&nbsp;";
        output += "</div>";
    } else {
        output += '<div class="clocky">';
        output += "Time Remaining<br/>";
        output += "" + o.days + "D&nbsp;";
        output += "" + o.hours + "H&nbsp;";
        output += ": " + o.minutes + "M&nbsp;";
        output += "</div>";
    }

    return output;
}

function calcTime(dateTimeStr) {
    let inputDate = new Date(dateTimeStr);
    let now = new Date();
    let diffMs = Math.abs(now - inputDate); // Difference in milliseconds
    let expired = now > inputDate; // Check if the datetime has passed

    let days = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    let hours = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    let minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

    return { days, hours, minutes, expired };
}

function jaudio(fileName) {

    if (fileName.trim()) {
        let url = `/webhook_data/cc/${fileName}`;
        return `<audio class='audioey' controls><source src="${url}" type="audio/mpeg">Your browser does not support the audio element.</audio>`;
    }
    return "<div class='audioey'>No Audio</div>";
}


// Add the class "pop-image" to any image that you want to pop up and view
$(document).ready(function() {
    $('.pop-image').on('click', function() {
        var src = $(this).attr('src');
        $('#overlay-image').attr('src', src);
        $('#overlay').addClass('show').fadeIn();
    });

    $('#close-icon').on('click', function() {
        $('#overlay').removeClass('show').fadeOut();
    });

    // Optional: Close the overlay when clicking outside the image
    $('#overlay').on('click', function(e) {
        if (e.target === this) {
            $('#overlay').removeClass('show').fadeOut();
        }
    });



});


// From TMV..

$(document).ready(function () {
    // Create and append the modal structure to the body
    const modal = $(
        `<div id="viewerModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 10000; overflow: auto;">
                            <div style="background: #fff; margin: 50px auto; padding: 0; width: 90%; max-width: 1000px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
                                <div id="modalHeader" style="height: 48px; background: #f4f4f4; display: flex; justify-content: space-between; align-items: center; padding: 0 10px; border-bottom: 1px solid #ccc;">
                                    <button id="closeModal" style="background: #ff5c5c; color: white; border: none; padding: 5px 10px; cursor: pointer;">Close</button>
                                    <button id="printImage" style="background: #5c9eff; color: white; border: none; padding: 5px 10px; cursor: pointer; display: none;">Print</button>
                                </div>
                                <div id="modalContent" style="position: relative; max-height: calc(100vh - 98px); overflow: auto;"></div>
                            </div>
                        </div>`
    );

    $('body').append(modal);

    // Close modal functionality
    $(document).on('click', '#closeModal', function () {
        $('#viewerModal').fadeOut();
        $('#modalContent').empty();
        $('#printImage').hide();
    });

    // Print image functionality
    $(document).on('click', '#printImage', function () {
        const imgSrc = $('#modalContent img').attr('src');
        if (imgSrc) {
            const printWindow = window.open('');
            printWindow.document.write(`<img src="${imgSrc}" style="width: 100%;">`);
            printWindow.document.close();
            printWindow.focus();
            window.setTimeout(function () {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
    });

    // Event handler for viewer links
    $(document).on('click', 'a.viewer', function (e) {
        e.preventDefault();

        const href = $(this).attr('href');
        const rel = $(this).attr('rel');

        if (rel === 'pdf') {
            // Create iframe for PDF
            // $('#modalContent').html(
            //     `<iframe src="${href}" style="width: 100%; height: calc(100vh - 90px); border: none;"></iframe>`
            // );

            const encodedUrl = encodeURIComponent(href);
            $('#modalContent').html(`<iframe src="/proxyx.php?url=${encodedUrl}" style="width: 100%; height: calc(100vh - 90px); border: none;"></iframe>`);

            $('#printImage').hide();
        } else if (rel === 'image') {
            // Create image element
            $('#modalContent').html(
                `<img src="${href}" style="max-width: 100%; height: auto; display: block; margin: 0 auto;">`
            );
            $('#printImage').show();
        }

        // Show modal
        $('#viewerModal').fadeIn();
    });
});

// ^ From TMV