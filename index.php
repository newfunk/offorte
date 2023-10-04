<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Product Selection Form</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

  <div class="container mt-5">
    <h1>Request a quote</h1>
    <form id="productForm">
      <!-- Personal Information Fields -->
      <div class="mb-3">
        <input type="text" class="form-control" id="firstname" placeholder="First Name">
      </div>
      <div class="mb-3">
        <input type="text" class="form-control" id="lastname" placeholder="Last Name">
      </div>
      <!-- Add other fields in the same manner -->
      <div class="mb-3">
        <input type="text" class="form-control" id="email" placeholder="E-mail">
      </div>

      <div id="productSelectionArea">
        <!-- Product selection rows will be added here -->
      </div>
      <button type="button" class="btn btn-outline-secondary mt-3" id="addProductButton">Add Product</button>
      <button type="submit" class="btn btn-primary mt-3">Submit</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  document.addEventListener("DOMContentLoaded", function() {
    let productsData = [];
    let uniqueProductTypes = new Set();

    // Fetch product data
    fetch('https://static.account.yourhosting.nl/products/product-list.json')
      .then(response => response.json())
      .then(data => {
        productsData = data.products;
        productsData.forEach(product => {
          if (product.type !== 'reseller-hosting' && product.type !== 'reseller-discount') {
            uniqueProductTypes.add(product.type);
          }
        });
        addProductRow();
      })
      .catch(error => {
        console.error('Error fetching data:', error);
      });

    // Function to add a new product row
    function addProductRow() {
      const productRow = document.createElement('div');
      productRow.className = 'row mt-3';

      const typeSelect = document.createElement('select');
      typeSelect.className = 'form-select col';
      typeSelect.onchange = populateProductNames;
      typeSelect.innerHTML = '<option value="" disabled selected>Select Product Type</option>';
      uniqueProductTypes.forEach(type => {
        const option = document.createElement('option');
        option.value = type;
        option.textContent = type;
        typeSelect.appendChild(option);
      });

      const nameSelect = document.createElement('select');
      nameSelect.className = 'form-select col';
      nameSelect.innerHTML = '<option value="" disabled selected>Select Product Name</option>';

      const amountInput = document.createElement('input');
      amountInput.className = 'form-control col';
      amountInput.type = 'number';
      amountInput.placeholder = 'Amount';

      productRow.appendChild(typeSelect);
      productRow.appendChild(nameSelect);
      productRow.appendChild(amountInput);

      document.getElementById('productSelectionArea').appendChild(productRow);
    }

    // Function to populate product names based on selected type
    function populateProductNames(event) {
      const selectedType = event.target.value;
      const nameSelect = event.target.nextElementSibling;
      nameSelect.innerHTML = '<option value="" disabled selected>Select Product Name</option>';
      productsData.forEach(product => {
        if (product.type === selectedType) {
          const option = document.createElement('option');
          option.value = product.name;
          option.textContent = product.name;
          nameSelect.appendChild(option);
        }
      });
    }

    // Attach the click event listener for the "Add Product" button
    document.getElementById('addProductButton').addEventListener('click', addProductRow);

    // Handle form submission
    document.getElementById('productForm').addEventListener('submit', function(event) {
      event.preventDefault();

      const requiredFields = ['firstname', 'lastname', 'email'];
      const formData = {};
      for (const field of requiredFields) {
        const value = document.getElementById(field).value;
        if (!value) {
          alert('All fields must be filled out');
          return;
        }
        formData[field] = value;
      }

      const rows = document.querySelectorAll('#productSelectionArea .row');
      const selectedProducts = [];
      rows.forEach(row => {
        const typeSelect = row.children[0];
        const nameSelect = row.children[1];
        const amountInput = row.children[2];
        if (typeSelect.value && nameSelect.value && amountInput.value) {
          selectedProducts.push({
            type: typeSelect.value,
            name: nameSelect.value,
            amount: parseInt(amountInput.value)
          });
        }
      });

      const payload = JSON.stringify({
        personalInfo: formData,
        products: selectedProducts
      });

      fetch('https://webhook.site/3f5343ba-9cf4-4cd7-80ed-fcf88d3f92a3', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: payload
      })
      .then(response => {
        if (response.ok) {
          return response.text();
        } else {
          return Promise.reject(`Server returned ${response.status}: ${response.statusText}`);
        }
      })
      .then(data => {
        console.log('Success:', data);

        // Show the success modal
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();

        // Clear the form
        document.getElementById('productForm').reset();
        document.getElementById('productSelectionArea').innerHTML = '';
        addProductRow();  // Add an empty product row
      })
      .catch(error => {
        console.error('Error:', error);
      });
    });
  });


  </script>
  <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="successModalLabel">Success</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Your form has been successfully submitted!
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
