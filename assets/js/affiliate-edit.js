/**
 * File: affiliate-edit.js
 * Description: Handles commission settings and form interactions for editing an affiliate.
 * Used in: admin.php?page=edit-affiliate
 */
document.addEventListener('DOMContentLoaded', function() {
    const commissionTypeSelect = document.getElementById('commission_type_select');
    const commissionSections = {
        product: document.getElementById('commission_by_product'),
        order: document.getElementById('commission_by_order'),
        quantity: document.getElementById('commission_by_quantity')
    };

    function handleCommissionTypeChange(selectedType) {
        Object.keys(commissionSections).forEach(type => {
            commissionSections[type].style.display = (type === selectedType) ? 'block' : 'none';
        });
    }

    if (commissionTypeSelect) {
        commissionTypeSelect.addEventListener('change', function() {
            handleCommissionTypeChange(this.value);
        });

        handleCommissionTypeChange(commissionTypeSelect.value);
    }

    // Handle adding/removing commission sections
    function handleAddCommissionSection(buttonId, containerId, className) {
        const addButton = document.getElementById(buttonId);
        if (addButton) {
            addButton.addEventListener('click', function() {
                const container = document.getElementById(containerId);
                const newSection = container.querySelector(`.${className}`).cloneNode(true);
                newSection.querySelectorAll('input, select, textarea').forEach(input => input.value = '');
                container.appendChild(newSection);

                newSection.querySelector(`.remove-${className}`).addEventListener('click', function() {
                    newSection.remove();
                });
            });
        }
    }

    handleAddCommissionSection('add_product_commission', 'commission_by_product', 'product-commission');
    handleAddCommissionSection('add_quantity_commission', 'commission_by_quantity', 'quantity-commission');
});
