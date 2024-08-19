// 在 my-affiliate-scripts.js 文件中
jQuery(document).ready(function($) {
    $('form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        // 显示加载图标
        form.find('.loading-icon').show();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                // 隐藏加载图标
                form.find('.loading-icon').hide();

                if (response.success) {
                    alert('Success: ' + response.data.message);
                } else {
                    alert('Error: ' + response.data.message);
                }
            }
        });
    });
});
//commission option
document.addEventListener('DOMContentLoaded', function () {
    const commissionTypeSelect = document.getElementById('commission_type_select');
    const commissionByProduct = document.getElementById('commission_by_product');
    const commissionByOrder = document.getElementById('commission_by_order');
    const commissionByQuantity = document.getElementById('commission_by_quantity');

    // Function to handle the display of commission sections
    function handleCommissionTypeChange(selectedType) {
        commissionByProduct.style.display = selectedType === 'product' ? 'table-row' : 'none';
        commissionByOrder.style.display = selectedType === 'order' ? 'table-row' : 'none';
        commissionByQuantity.style.display = selectedType === 'quantity' ? 'table-row' : 'none';
    }

    // Event listener for commission type change
    commissionTypeSelect.addEventListener('change', function() {
        handleCommissionTypeChange(this.value);
    });

    // Initialize visibility on page load
    handleCommissionTypeChange(commissionTypeSelect.value);

    // Function to handle adding new product/quantity commission sections
    function handleAddCommissionSection(buttonId, containerId, className) {
        document.getElementById(buttonId).addEventListener('click', function () {
            const container = document.getElementById(containerId);
            const newSection = container.querySelector('.' + className).cloneNode(true);
            newSection.querySelectorAll('input, select, textarea').forEach(input => input.value = '');
            container.appendChild(newSection);

            newSection.querySelector('.remove-' + className).addEventListener('click', function () {
                newSection.remove();
            });
        });
    }

    handleAddCommissionSection('add_product_commission', 'commission_by_product', 'product-commission');
    handleAddCommissionSection('add_quantity_commission', 'commission_by_quantity', 'quantity-commission');
});
