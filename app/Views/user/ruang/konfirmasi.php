<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Notification Page
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
 </head>
 <body class="bg-white min-h-screen flex flex-col">
  <header class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
   <button aria-label="Menu" class="text-black text-xl">
    <i class="fas fa-bars">
    </i>
   </button>
   <div class="text-black text-base font-normal">
    Label
   </div>
   <button aria-label="User menu" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
    <img alt="User icon with small shapes inside circle" class="w-4 h-4" height="16" src="https://storage.googleapis.com/a1aa/image/1eca0708-e427-4e1d-d3d2-9b93b8906b93.jpg" width="16"/>
   </button>
  </header>
  <main class="flex-grow flex items-center justify-center px-4">
   <div class="max-w-xs w-full border border-gray-400 rounded-md p-4 relative">
    <div class="flex items-start justify-between mb-2">
     <div class="flex items-center space-x-2">
      <i class="fas fa-info-circle text-black text-sm">
      </i>
      <span class="font-semibold text-black text-sm leading-tight">
       Reservasi Berhasill
      </span>
     </div>
     <button aria-label="Close notification" class="text-black text-sm leading-none">
      <i class="fas fa-times">
      </i>
     </button>
    </div>
    <p class="text-black text-sm leading-relaxed mb-3">
     Silahkan tunggu konfirmasi dari admin
    </p>
    <button onclick="window.location.href='/home'" class="bg-gray-800 text-white text-sm rounded-md px-3 py-1 hover:bg-gray-700 transition">
    Close
    </button>
   </div>
  </main>
 </body>
</html>
