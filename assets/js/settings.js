document.addEventListener('DOMContentLoaded', function () {
    const commissionTypeSelect = document.getElementById('commission_type_select');
    const commissionByProduct = document.getElementById('commission_by_product');
    const commissionByOrder = document.getElementById('commission_by_order');
    const commissionByQuantity = document.getElementById('commission_by_quantity');

    function handleCommissionTypeChange() {
        const selectedType = commissionTypeSelect.value;
        console.log("Selected type:", selectedType); // Debugging line

        commissionByProduct.style.display = selectedType === 'product' ? 'table-row' : 'none';
        commissionByOrder.style.display = selectedType === 'order' ? 'table-row' : 'none';
        commissionByQuantity.style.display = selectedType === 'quantity' ? 'table-row' : 'none';
    }

    commissionTypeSelect.addEventListener('change', handleCommissionTypeChange);

    // Initialize on page load
    handleCommissionTypeChange();
});
