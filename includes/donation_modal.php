<div class="modal fade" id="donationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gift me-2 text-success"></i>Donation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"><dl class="row mb-0" id="donationDetails"></dl></div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('donationModal');
    modal.addEventListener('show.bs.modal', function (ev) {
        var data = JSON.parse(ev.relatedTarget.getAttribute('data-details'));
        var dl = document.getElementById('donationDetails');
        dl.innerHTML = '';
        Object.keys(data).forEach(function (label) {
            var dt = document.createElement('dt'), dd = document.createElement('dd');
            dt.className = 'col-sm-4';
            dd.className = 'col-sm-8';
            dd.style.whiteSpace = 'pre-wrap';
            dt.textContent = label;
            dd.textContent = data[label];
            dl.appendChild(dt);
            dl.appendChild(dd);
        });
    });
});
</script>
