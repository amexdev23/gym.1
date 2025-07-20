<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Header</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">


  <!-- Second Header (Main Section) -->
  <main class="flex-1 p-6 lg:p-0 ml-[250px]">
    <div class="flex justify-between items-center p-4 mb-6 bg-white shadow ml-[-30px]">
      <h1 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle; ?></h1>
      <div class="flex items-center space-x-4">
        <div class="relative">
          <input 
            type="text" 
            placeholder="Search..."
            class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <span class="absolute top-2.5 right-3 text-gray-400">
            <i class="fas fa-search"></i>
          </span>
        </div>
        <div class="relative">
          <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-9 h-9 bg-green-500 text-white flex items-center justify-center rounded-full font-semibold">A</div>
          <span class="font-medium text-gray-700">Admin</span>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
