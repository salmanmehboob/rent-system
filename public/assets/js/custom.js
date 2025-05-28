// ===================== GLOBAL FUNCTIONS ===================== //

// Global reusable dropdown loader based on category/building/etc.
function loadDependentDropdown(url, dataKey, targetDropdownId, selectedValue = null, placeholder = "Select Option") {
    let dropdown = $(`#${targetDropdownId}`);
    dropdown.empty().append('<option value="">Loading...</option>');

    if (dataKey) {
        $.ajax({
            url: `${url}/${dataKey}`, // <-- FIXED: Append dataKey directly to URL
            type: 'GET',
            data: { id: dataKey },
            success: function (data) {
                dropdown.empty().append(`<option value="">${placeholder}</option>`);
                $.each(data, function (index, item) {
                    dropdown.append(
                        `<option value="${item.id}">${item.name}</option>`
                    );
                });

                if (selectedValue) {
                    dropdown.val(selectedValue).trigger('change');
                }

                dropdown.trigger('change'); // If using select2
            },
            error: function () {
                dropdown.empty().append(`<option value="">Error loading ${placeholder.toLowerCase()}</option>`);
            }
        });
    } else {
        dropdown.empty().append(`<option value="">${placeholder}</option>`);
    }
}
// ===================== END GLOBAL FUNCTIONS ===================== //

// ===================== DOCUMENT READY ===================== //
$(document).ready(function () {
    // Check if jQuery is loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded.');
        return;
    }

    // Check if Toastr is loaded
    if (typeof toastr === 'undefined') {
        console.error('Toastr is not loaded.');
        return;
    }

// Access session messages from the global `window.sessionMessages` object
    const messages = window.sessionMessages || {};

    if (messages.success) {
        toastr.success(messages.success, "Success", {
            timeOut: 3000,
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: false
        });
    }

    if (messages.error) {
        toastr.error(messages.error, "Error", {
            timeOut: 3000,
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: false
        });
    }

    if (messages.info) {
        toastr.info(messages.info, "Info", {
            timeOut: 3000,
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: false
        });
    }

    if (messages.warning) {
        toastr.warning(messages.warning, "Warning", {
            timeOut: 3000,
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: false
        });
    }


    // Initialize DataTables
    if ($.fn.dataTable) {

        $('.datatable').DataTable({
            createdRow: function (row, data, index) {
                $(row).addClass('selected')
            },
            language: {
                paginate: {
                    next: '>',
                    previous: '<'
                }
            }
        });
    } else {
        console.error('DataTables is not loaded.');
    }


    if ($.fn.select2) {
        $('.select2').select2({
            placeholder: "Select Option"
        });

       $('.select2-multiple').select2({
    placeholder: "Select Option",
    allowClear: true
});
    } else {
        console.error('Select2 is not loaded.');
    }

    // Initialize Select2

///////////////////////////////////AJAX Function Started/////////////////////////////////////
    $(document).on('click', '.delete-record', function (e) {
        e.preventDefault();

        const url = $(this).data('url'); // Get delete URL
         const dataTable = $(this).data('table');   // Get record ID


        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: "DELETE",
                    data: {
                        "_token": CSRF_TOKEN
                    },
                    success: function (response) {
                        if (response.success) {
                            toastr.success(response.success, "Deleted!", {
                                timeOut: 2000,
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-top-right",
                                preventDuplicates: true
                            });


                            // Reload the DataTable on success
                            setTimeout(function () {
                                $('#' + dataTable).DataTable().ajax.reload(null, false);
                            }, 1000);


                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.error,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                 error: function (xhr) {
                        let message = 'Something went wrong. Please try again later.';
                        let swalTitle = 'Error!';
                        let swalIcon = 'error';

                        if (xhr.status === 400 && xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.global) {
                            message = xhr.responseJSON.errors.global[0];
                            // swalTitle = 'Warning!';
                            // swalIcon = 'warning';
                            // Also show in the form error div if needed
                        $('#formError').removeClass('d-none').text(message);
                        
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            message = xhr.responseJSON.error;
                             Swal.fire({
                                title: swalTitle,
                                text: message,
                                icon: swalIcon,
                                confirmButtonText: 'OK'
                            });
                        }

                        // Swal.fire({
                        //     title: swalTitle,
                        //     text: message,
                        //     icon: swalIcon,
                        //     confirmButtonText: 'OK'
                        // });

                        
                    }


                });
            }
        });
    });

    $(document).on('submit', '.ajax-form', function (e) {
        e.preventDefault();

        let form = $(this);
        let datatable = form.data('table');
        let url = form.attr('action');
        let submitBtn = form.find('.submit-btn');
        let errorContainer = form.find('.text-danger');

        // Create a FormData object for file upload
        let formData = new FormData(this);

        // Clear any previous errors
        errorContainer.text('');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: url,
            method: "POST",
            data: formData,
            processData: false,  // Prevent jQuery from automatically transforming the data into a query string
            contentType: false,  // Let the browser set the content type (multipart/form-data)
            success: function (response) {
                toastr.success(response.success, "Success!", {
                    timeOut: 2000,
                    closeButton: true,
                    progressBar: true,
                    positionClass: "toast-top-right",
                    preventDuplicates: true
                });

                // Reload the DataTable after 1 second
                setTimeout(function () {
                    $('#' + datatable).DataTable().ajax.reload(null, false);
                }, 1000);

                // Reset the form
                form[0].reset();

                // Reset all select2 fields within the form
                form.find('.select2').val(null).trigger('change');

                // Hide cancel button and uploaded image within the same form
                form.find('#cancelBtn').addClass('d-none');
                form.find('#uploadedImage').attr('src', '').addClass('d-none');

                // Reset jQuery repeater with one default item
                    let repeater = $('#witnessRepeater');
                    let list = repeater.find('[data-repeater-list]');
                    list.html(''); // Clear all items
                    list.append(`
                        <div data-repeater-item>
                            <div class="row">
                                <div class="col-md-5 mx-5">
                                    <input type="text" name="name" placeholder="Name" class="form-control mb-2">
                                    <input type="text" name="mobile_no" placeholder="Mobile No" class="form-control mb-2">
                                    <button data-repeater-delete type="button" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="cnic" placeholder="CNIC" class="form-control mb-2">
                                    <input type="text" name="address" placeholder="Address" class="form-control mb-2">
                                </div>
                            </div>
                            <hr>
                        </div>
                    `);

                    // Reinitialize the repeater plugin
                    repeater.repeater({
                        initEmpty: false
                    });

                // Reset submit button state
                submitBtn.prop('disabled', false).text('Save');
            },
            // error: function (xhr) {
            //     $(".text-danger").html(""); // clear previous errors
            //     $("#formError").addClass("d-none").html(""); // clear global error

            //     if (xhr.status === 422) {
            //         const response = xhr.responseJSON;

            //         // Field-specific errors
            //         if (response.errors) {
            //             $.each(response.errors, function (key, messages) {
            //                 form.find(`#${key}Error`).text(messages[0]);
            //             });
            //         }

            //         // Special global message check
            //         if (response.global) {
            //             $("#formError").removeClass("d-none").html(response.global[0]);
            //         }
            //     } else {
            //         errorContainer.removeClass('d-none').text('An unexpected error occurred.');
            //     }
            // }

           error: function (xhr) {
                $(".text-danger").html(""); // clear previous errors
                $("#formError").addClass("d-none").html(""); // clear global error

                // Re-enable submit button
                submitBtn.prop('disabled', false).text('Save'); // ✅ add this

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    // Field-specific errors
                    $.each(errors, function (key, value) {
                        $("#" + key + "Error").html(value[0]);
                    });

                    // Special global message check
                    if (errors.global) {
                        $("#formError").removeClass("d-none").html(errors.global[0]);
                    }
                } else {
                    // General error fallback
                    $("#formError").removeClass("d-none").html("An unexpected error occurred.");
                }
            }

        });
    });




//==================================AJAX Ended==================================================//
 


});
