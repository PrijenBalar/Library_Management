/* =========================================================
   Online Library Management System - jQuery / AJAX scripts
   ========================================================= */
$(function () {

    /* ---------- Menu bar: mobile toggle + dropdowns ---------- */
    $('#menuToggle').on('click', function () {
        $('#mainMenu').toggleClass('open');
    });
    $('#sidebarToggle').on('click', function (e) {
        e.stopPropagation();
        $('#sidebar').toggleClass('open');
    });
    $(document).on('click', function (e) {
        if ($('#sidebar').hasClass('open') && !$(e.target).closest('#sidebar').length) {
            $('#sidebar').removeClass('open');
        }
    });
    $(document).on('click', '.dd-toggle', function (e) {
        e.preventDefault();
        var li = $(this).parent();
        $('.menu > li.open').not(li).removeClass('open');
        li.toggleClass('open');
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.menu > li').length) {
            $('.menu > li.open').removeClass('open');
        }
    });

    /* ---------- Auto hide alerts after 5 seconds ---------- */
    setTimeout(function () {
        $('.alert.auto-hide').fadeOut(400);
    }, 5000);

    /* ---------- Delete confirmation ---------- */
    $(document).on('click', '.confirm-delete', function (e) {
        if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    /* ---------- Show / hide password ---------- */
    $(document).on('click', '.pw-toggle', function () {
        var input = $(this).siblings('input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).text('Hide');
        } else {
            input.attr('type', 'password');
            $(this).text('Show');
        }
    });

    /* ---------- Password confirmation check (any form with #password + #confirmpassword) ---------- */
    $(document).on('submit', 'form.check-password', function (e) {
        var p = $(this).find('#password').val();
        var c = $(this).find('#confirmpassword').val();
        if (p !== c) {
            alert('Password and Confirm Password do not match.');
            e.preventDefault();
            return false;
        }
        if (p.length < 6) {
            alert('Password must be at least 6 characters long.');
            e.preventDefault();
            return false;
        }
    });

    /* ---------- Student side: live book search (AJAX) ---------- */
    var bookTimer = null;
    $('#bookSearch').on('keyup', function () {
        var q = $(this).val();
        clearTimeout(bookTimer);
        bookTimer = setTimeout(function () {
            $.ajax({
                url: 'ajax/search-books.php',
                type: 'GET',
                data: { q: q },
                beforeSend: function () {
                    $('#booksGrid').css('opacity', 0.5);
                },
                success: function (html, status, xhr) {
                    $('#booksGrid').html(html).css('opacity', 1);
                    var n = xhr.getResponseHeader('X-Book-Count');
                    if (n !== null) { $('#bookCount').text(n + ' book(s)'); }
                },
                error: function () {
                    $('#booksGrid').html('<p class="empty">Something went wrong.</p>').css('opacity', 1);
                }
            });
        }, 300);
    });

    /* ---------- Admin: Issue book page - fetch student by ID (AJAX) ---------- */
    // Only on the admin Issue Book page (it has #studentHint). The student login box on the
    // home page also uses id="studentid", so without this check it would call the admin lookup.
    $('#studentHint').length && $('#studentid').on('blur keyup', function () {
        var sid = $(this).val().trim();
        if (sid.length < 5) {
            $('#studentName').val('').removeClass('ok err');
            $('#studentHint').text('').removeClass('ok err');
            return;
        }
        $.getJSON('ajax/get-student.php', { studentid: sid }, function (res) {
            if ($('#studentid').val().trim() !== sid) { return; } // ignore a reply for older text
            if (res.status === 'ok') {
                $('#studentName').val(res.data.FullName);
                $('#studentHint').text('Student found: ' + res.data.FullName + ' (' + res.data.EmailId + ')').removeClass('err').addClass('ok');
            } else {
                $('#studentName').val('');
                $('#studentHint').text(res.message).removeClass('ok').addClass('err');
            }
        });
    });

    /* ---------- Admin: Issue book page - fetch book by ISBN (AJAX) ---------- */
    // Only on the admin Issue Book page (it has #bookHint), not on Add / Edit Book.
    $('#bookHint').length && $('#isbn').on('blur keyup', function () {
        var isbn = $(this).val().trim();
        if (isbn.length < 3) {
            $('#bookName').val('');
            $('#bookHint').text('').removeClass('ok err');
            return;
        }
        $.getJSON('ajax/get-book.php', { isbn: isbn }, function (res) {
            if ($('#isbn').val().trim() !== isbn) { return; } // ignore a reply for older text
            if (res.status === 'ok') {
                $('#bookName').val(res.data.BookName);
                $('#bookId').val(res.data.id);
                var msg = 'Book found: ' + res.data.BookName + ' by ' + res.data.AuthorName;
                if (res.data.issued) {
                    $('#bookHint').text(msg + ' - Currently issued to ' + res.data.issued).removeClass('ok').addClass('err');
                } else {
                    $('#bookHint').text(msg + ' - Available').removeClass('err').addClass('ok');
                }
            } else {
                $('#bookName').val('');
                $('#bookId').val('');
                $('#bookHint').text(res.message).removeClass('ok').addClass('err');
            }
        });
    });

    /* ---------- Admin: Search student page (AJAX) ---------- */
    $('#studentSearchForm').on('submit', function (e) {
        e.preventDefault();
        var q = $('#searchStudent').val().trim();
        if (q === '') {
            $('#searchResult').html('<div class="alert alert-warning">Please enter a Student ID, name or email.</div>');
            return;
        }
        $.ajax({
            url: 'ajax/search-student.php',
            type: 'GET',
            data: { q: q },
            beforeSend: function () {
                $('#searchResult').html('<p class="text-muted">Searching...</p>');
            },
            success: function (html) {
                $('#searchResult').html(html);
            },
            error: function () {
                $('#searchResult').html('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
            }
        });
    });

    /* ---------- Admin: Return book - auto calculate fine ---------- */
    function calcFine() {
        var overdue = parseInt($('#overdueDays').val() || '0', 10);
        var perDay = parseFloat($('#finePerDay').val() || '0');
        if ($('#fine').length && !$('#fine').data('touched')) {
            $('#fine').val((overdue * perDay).toFixed(2));
        }
    }
    $('#fine').on('input', function () { $(this).data('touched', true); });
    calcFine();

    /* ---------- Admin: Manage table quick filter (client side) ---------- */
    $('#tableFilter').on('keyup', function () {
        var v = $(this).val().toLowerCase();
        $('.filter-table tbody tr').each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(v) > -1);
        });
    });

    /* =====================================================
       BOOK SELLING MODULE
       ===================================================== */
    function toast(msg, type) {
        $('.toast').remove();
        var t = $('<div class="toast"></div>').addClass(type).text(msg).appendTo('body');
        setTimeout(function () { t.fadeOut(400, function () { t.remove(); }); }, 3000);
    }
    function setCartCount(n) {
        if (n !== undefined) { $('#cartCount, #cartCountTop').text(n); }
    }
    function cartCall(data, done) {
        $.ajax({ url: 'ajax/cart.php', type: 'POST', data: data, dataType: 'json' })
            .done(function (res) {
                setCartCount(res.count);
                done(res);
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong. Please try again.';
                toast(msg, 'danger');
            });
    }

    /* Add to cart (Buy Books page) */
    $(document).on('click', '.add-to-cart', function () {
        var btn = $(this), id = btn.data('id');
        var qty = parseInt($('#qty-' + id).val() || '1', 10);
        btn.prop('disabled', true).text('Adding...');
        cartCall({ action: 'add', book_id: id, qty: qty }, function (res) {
            toast(res.message, res.status === 'ok' ? 'success' : 'danger');
        });
        setTimeout(function () { btn.prop('disabled', false).text('Add to Cart'); }, 600);
    });

    /* Change quantity (Cart page) */
    $(document).on('change', '.cart-qty', function () {
        var input = $(this), id = input.data('id');
        cartCall({ action: 'update', book_id: id, qty: input.val() }, function (res) {
            if (res.status === 'ok') {
                input.data('prev', input.val());
                $('#line-' + id).text(res.line_total);
                $('#cartTotal').text(res.cart_total);
            } else {
                input.val(input.data('prev'));
                toast(res.message, 'danger');
            }
        });
    });

    /* Remove from cart (Cart page) */
    $(document).on('click', '.cart-remove', function () {
        if (!confirm('Remove this book from your cart?')) { return; }
        var id = $(this).data('id');
        cartCall({ action: 'remove', book_id: id }, function (res) {
            if (res.status === 'ok') {
                $('#row-' + id).fadeOut(300, function () { $(this).remove(); });
                $('#cartTotal').text(res.cart_total);
                toast(res.message, 'success');
                if (res.count === 0) { setTimeout(function () { location.reload(); }, 600); }
            } else {
                toast(res.message, 'danger');
            }
        });
    });

    /* Checkout: address is only needed for Cash on Delivery */
    function toggleAddress() {
        var cod = $('input[name="payment"]:checked').val() === 'Cash on Delivery';
        $('#addressGroup').toggle(cod);
        $('#address').prop('required', cod);
    }
    if ($('#checkoutForm').length) {
        $('input[name="payment"]').on('change', toggleAddress);
        toggleAddress();
    }

    /* Admin: preview the chosen book picture */
    $(document).on('change', '.img-input', function () {
        var file = this.files && this.files[0];
        if (!file) { $('#imgPreview').prop('hidden', true); return; }
        var reader = new FileReader();
        reader.onload = function (ev) { $('#imgPreview').attr('src', ev.target.result).prop('hidden', false); };
        reader.readAsDataURL(file);
    });
});
