<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Order List</title>
</head>
<body class="bg-white">
<div class="max-w-7xl mx-auto relative">
  <div class="fixed top-0 w-full max-w-7xl bg-white z-10">
    <table class="w-full table-fixed border-b border-gray-100">
      <colgroup>
        <col class="w-1/6" />
        <col class="w-1/6" />
        <col class="w-1/4" />
        <col class="w-1/4" />
        <col class="w-1/6" />
      </colgroup>
      <thead class="bg-white shadow">
        <tr>
          <th class="px-6 py-3 text-sm font-medium text-gray-500 text-center">Order</th>
          <th class="px-6 py-3 text-sm font-medium text-gray-500 text-center">Type</th>
          <th class="px-6 py-3 text-sm font-medium text-gray-500 text-left">Pesanan</th>
          <th class="px-6 py-3 text-sm font-medium text-gray-500 text-left">Keterangan</th>
          <th class="px-6 py-3 text-sm font-medium text-gray-500 text-left">Status</th>
        </tr>
      </thead>
    </table>
  </div>

  <div class="px-4">
    <div class="space-y-4 pt-14" id="ordersContainer"></div>
  </div>
</div>

<script>
  let orders = [];

  const categoryColors = {
    1: 'bg-orange-100 text-orange-800', // Example mapping for category IDs
    2: 'bg-blue-100 text-blue-800',
    3: 'bg-green-100 text-green-800',
  };

  const typeColors = {
    'dine-in': 'bg-blue-100 text-blue-800',
    'takeaway': 'bg-orange-100 text-orange-800',
  };

  const typeLabels = {
    'dine-in': 'Dine In',
    'takeaway': 'Takeaway'
  };

  async function fetchOrders() {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/transactions');
      const data = await response.json();
      if (data.success) {
        orders = data.data.map(transaction => ({
          time: new Date(transaction.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
          date: new Date(transaction.created_at).toLocaleDateString('id-ID'),
          type: 'dine-in', // Assuming default type is dine-in; adjust based on your data
          items: transaction.details.map(detail => ({
            name: detail.outlet_product.product.name,
            notes: detail.notes || '',
            status: transaction.status === 'pending' ? 'process' : 'done',
            category: detail.outlet_product.product.category_id
          }))
        }));
        renderOrders();
      }
    } catch (error) {
      console.error('Error fetching orders:', error);
    }
  }

  function toggleStatus(orderIndex, itemIndex) {
    const item = orders[orderIndex].items[itemIndex];
    item.status = (item.status === 'done') ? 'process' : 'done';
    renderOrders();
  }

  function renderOrders() {
    const container = document.getElementById('ordersContainer');
    container.innerHTML = '';

    orders.forEach((order, oIndex) => {
      const card = document.createElement('div');
      card.className = 'bg-white rounded-lg shadow-xl';

      const table = document.createElement('table');
      table.className = 'w-full table-fixed border-collapse rounded-lg';

      table.innerHTML = `
        <colgroup>
          <col class="w-1/6" />
          <col class="w-1/6" />
          <col class="w-1/4" />
          <col class="w-1/4" />
          <col class="w-1/6" />
        </colgroup>
      `;

      const tbody = document.createElement('tbody');

      const allDone = order.items.every(i => i.status === 'done');
      const isSingleItem = order.items.length === 1;

      order.items.forEach((item, iIndex) => {
        const tr = document.createElement('tr');
        if (iIndex < order.items.length - 1) {
          tr.className = 'border-b border-gray-200';
        }

        if (iIndex === 0) {
          const tdOrder = document.createElement('td');
          tdOrder.className = `px-4 sm:px-6 py-6 ${isSingleItem ? 'align-middle' : 'align-top'} text-center`;
          tdOrder.rowSpan = order.items.length;
          tdOrder.innerHTML = `
            <div class="rounded-lg p-2 sm:p-4 space-y-3 flex flex-col items-center">
              <div class="text-lg font-medium text-gray-900">Order #${orders.length - oIndex}</div>
              <div class="text-2xl font-bold text-gray-900">${order.time}</div>
              <div class="w-full">
                <span class="inline-block w-full px-3 py-2 rounded-full text-sm font-medium
                  ${allDone ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                  ${allDone ? 'All Done' : 'In Progress'}
                </span>
              </div>
              <div class="text-sm text-gray-500">${order.date}</div>
            </div>
          `;
          tr.appendChild(tdOrder);

          const tdType = document.createElement('td');
          tdType.className = 'px-4 sm:px-6 py-4 align-middle text-center';
          tdType.rowSpan = order.items.length;
          tdType.innerHTML = `
            <div class="flex items-center justify-center h-full">
              <span class="w-full sm:w-auto px-4 py-2 rounded-md text-sm font-medium ${typeColors[order.type]}">
                ${typeLabels[order.type]}
              </span>
            </div>
          `;
          tr.appendChild(tdType);
        }

        const tdPesanan = document.createElement('td');
        tdPesanan.className = `px-4 sm:px-6 py-4 ${isSingleItem ? 'align-middle' : 'align-top'}`;
        tdPesanan.innerHTML = `
          <div class="shadow rounded-md px-4 py-2 ${categoryColors[item.category]} hover:shadow-md transition-shadow duration-200">
            ${item.name}
          </div>
        `;
        tr.appendChild(tdPesanan);

        const tdKeterangan = document.createElement('td');
        tdKeterangan.className = `px-4 sm:px-6 py-4 ${isSingleItem ? 'align-middle' : 'align-top'} text-sm text-gray-700`;
        tdKeterangan.innerHTML = item.notes
          ? `<div class="bg-white shadow rounded-md px-4 py-2 hover:shadow-md transition-shadow duration-200">
               ${item.notes}
             </div>`
          : '-';
        tr.appendChild(tdKeterangan);

        const tdStatus = document.createElement('td');
        tdStatus.className = `px-4 sm:px-6 py-4 ${isSingleItem ? 'align-middle' : 'align-top'}`;
        tdStatus.innerHTML = `
          <button
            onclick="toggleStatus(${oIndex}, ${iIndex})"
            class="w-full sm:w-auto px-3 py-2 rounded-md text-sm font-semibold transition-colors duration-200
            ${item.status === 'done' 
              ? 'bg-green-100 text-green-800 hover:bg-green-200' 
              : 'bg-yellow-50 text-yellow-800 hover:bg-yellow-100'}"
          >
            ${item.status === 'done' ? 'Done' : 'Process'}
          </button>
        `;
        tr.appendChild(tdStatus);

        tbody.appendChild(tr);
      });

      table.appendChild(tbody);
      card.appendChild(table);
      container.appendChild(card);
    });
  }

  fetchOrders();
</script>
</body>
</html>
