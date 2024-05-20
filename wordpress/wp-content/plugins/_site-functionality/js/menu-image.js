/**
 * Add event listeners for managing media in the WordPress admin area.
 *
 * This code adds event listeners to handle media management in the WordPress admin area.
 * Specifically, it opens the media manager when the "Manage Media" button is clicked.
 *
 */
document.addEventListener('DOMContentLoaded', function () {
    // Find the "Manage Media" button.
    const uploadButton = document.querySelector('.my-manage-media-button');
    const imagePreview = document.querySelector('.user-preview-image')
    const imageInput = document.querySelector('.menu-image-input')
    // Check if the button exists.
    if (uploadButton) {
        // Add a click event listener to the button.
        uploadButton.addEventListener('click', function (e) {
            e.preventDefault();
            console.log("click");
            // Create a WordPress media manager instance.
            const cpUploader = wp.media({
                button: {
                    text: 'Close'
                },
                states: [
                    new wp.media.controller.Library({
                        title: 'Select an Image',
                        filterable: 'all',
                        sortable: true,
                        multiple: true,
                        selectedImageId: imageInput.getAttribute("value"),
                        library: wp.media.query({
                            type: 'image',
                        })
                    })
                ]
            });

            // Handle the "select" event when media is selected.
            cpUploader.on('select', function () {
                // Get the selected attachment.
                const attachment = cpUploader.state().get('selection').first().toJSON();
                // No need to handle the selected attachment here, but you can add code as needed.
                console.log(attachment);
                imagePreview.setAttribute('src', attachment.url);
                imageInput.setAttribute('value', attachment.id);
            });

            // Open the media manager.
            cpUploader.open();
        });
    }
});