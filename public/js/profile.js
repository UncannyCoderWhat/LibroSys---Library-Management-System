/**
 * LibroSys - Profile Page JavaScript
 * Handles tab switching, receipts, borrow/return/reserve actions,
 * notifications, and tooltips.
 */

document.addEventListener('DOMContentLoaded', function () {
    switchTab('borrow');
});

function switchTab(tab) {
    document.getElementById('content-borrow').style.display = (tab === 'borrow') ? 'block' : 'none';
    document.getElementById('content-current-fines').style.display = (tab === 'current-fines') ? 'block' : 'none';
    document.getElementById('content-fines').style.display = (tab === 'fines') ? 'block' : 'none';

    const borrowHeader = document.getElementById('tab-borrow');
    const currentFinesHeader = document.getElementById('tab-current-fines');
    const finesHeader = document.getElementById('tab-fines');

    borrowHeader.style.borderBottom = "none";
    borrowHeader.style.color = "#888";
    currentFinesHeader.style.borderBottom = "none";
    currentFinesHeader.style.color = "#888";
    finesHeader.style.borderBottom = "none";
    finesHeader.style.color = "#888";

    const activeHeader = document.getElementById('tab-' + tab);
    if (activeHeader) {
        activeHeader.style.borderBottom = "3px solid var(--main-color)";
        activeHeader.style.color = "#000";
    }
}

function openReceiptModal() {
    document.getElementById('receiptModal').style.display = 'flex';
}

function closeReceiptModal() {
    document.getElementById('receiptModal').style.display = 'none';
}

function confirmPayment() {
    const formData = new FormData();
    formData.append('action', 'pay_fines');

    fetch('index.php?page=ajax&action=borrow_handler', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.status === 'success') {
            window.location.reload();
        }
    });
}

function processAction(actionType, bookId) {
    if(!confirm("Are you sure you want to borrow this book?")) return;

    const formData = new FormData();
    formData.append('action', 'borrow');
    formData.append('book_id', bookId);

    fetch('index.php?page=ajax&action=borrow_handler', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.status === 'success') {
            window.location.reload();
        }
    });
}

function cancelReservation(resId) {
    if(!confirm("Are you sure you want to cancel this reservation?")) return;

    const formData = new FormData();
    formData.append('borrow_id', resId);
    formData.append('action', 'cancel_reservation');

    fetch('index.php?page=ajax&action=borrow_handler', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json()).then(data => {
        alert(data.message);
        if(data.status === 'success') location.reload();
    });
}

function returnBook(borrowId) {
    if(!confirm("Are you sure you want to return this book?")) return;
    const formData = new FormData();
    formData.append('borrow_id', borrowId);
    fetch('index.php?page=ajax&action=return_handler', { method: 'POST', body: formData })
    .then(res => res.json()).then(data => {
        alert(data.message);
        location.reload();
    })
    .catch(err => alert("An error occurred while returning the book. Please check your connection."));
}

function markAsRead(id) {
    const formData = new FormData();
    formData.append('notification_id', id);
    fetch('index.php?page=ajax&action=mark_read', { method: 'POST', body: formData })
    .then(() => document.getElementById('notif-' + id).remove());
}

/**
 * Tooltip System
 * Adds hover tooltips to elements with class 'tooltip-trigger' and 'data-tooltip'.
 */
(function () {
    function createTooltipEl(text) {
        const el = document.createElement('div');
        el.className = 'tooltip-el';
        el.textContent = text;
        el.style.position = 'fixed';
        el.style.zIndex = 9999;
        el.style.background = 'rgba(0,0,0,0.85)';
        el.style.color = '#fff';
        el.style.padding = '8px 10px';
        el.style.borderRadius = '8px';
        el.style.fontSize = '12px';
        el.style.maxWidth = '320px';
        el.style.whiteSpace = 'normal';
        el.style.boxShadow = '0 10px 25px rgba(0,0,0,0.25)';
        return el;
    }

    function positionTooltip(el, anchorRect) {
        const padding = 12;
        el.style.left = (anchorRect.left + window.scrollX) + 'px';
        el.style.top = (anchorRect.top + window.scrollY - el.offsetHeight - padding) + 'px';
        if (parseFloat(el.style.top) < window.scrollY) {
            el.style.top = (anchorRect.bottom + window.scrollY + padding) + 'px';
        }
        const maxLeft = window.scrollX + document.documentElement.clientWidth - el.offsetWidth - padding;
        if (parseFloat(el.style.left) > maxLeft) {
            el.style.left = maxLeft + 'px';
        }
        if (parseFloat(el.style.left) < window.scrollX + padding) {
            el.style.left = (window.scrollX + padding) + 'px';
        }
    }

    function attachTooltip(trigger) {
        let tooltipEl = null;

        const show = () => {
            const text = trigger.getAttribute('data-tooltip') || '';
            if (!text) return;

            tooltipEl = createTooltipEl(text);
            document.body.appendChild(tooltipEl);
            const rect = trigger.getBoundingClientRect();
            positionTooltip(tooltipEl, rect);
        };

        const hide = () => {
            if (tooltipEl && tooltipEl.parentNode) {
                tooltipEl.parentNode.removeChild(tooltipEl);
            }
            tooltipEl = null;
        };

        trigger.addEventListener('mouseenter', show);
        trigger.addEventListener('mouseleave', hide);
        trigger.addEventListener('focus', show);
        trigger.addEventListener('blur', hide);

        window.addEventListener('scroll', () => {
            if (!tooltipEl) return;
            const rect = trigger.getBoundingClientRect();
            positionTooltip(tooltipEl, rect);
        }, { passive: true });

        window.addEventListener('resize', () => {
            if (!tooltipEl) return;
            const rect = trigger.getBoundingClientRect();
            positionTooltip(tooltipEl, rect);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const triggers = document.querySelectorAll('.tooltip-trigger[data-tooltip]');
        triggers.forEach(attachTooltip);
    });
})();
