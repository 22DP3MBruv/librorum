<template>
  <div class="px-4 py-8">
    <!-- Hero Section -->
    <div class="text-center mb-12">
      <h1 class="text-4xl font-bold text-gray-900 mb-4">
        Laipni lūdzam Librorum sistēmā!
      </h1>
      <p class="text-xl text-gray-600 max-w-2xl mx-auto">
        Grāmatu un diskusiju platforma, kas apvieno lasīšanas prieku ar kopienas sarunām.
        Sekojiet savam progresam, atklājiet jaunas grāmatas un dalieties viedokļos.
      </p>
    </div>

    <!-- Features Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="font-semibold text-gray-900 mb-2">Grāmatu pārvaldība</h3>
        <p class="text-gray-600 text-sm">
          Meklējiet grāmatas pēc nosaukuma, autora vai ISBN. Pievienojiet savas iecienītākās.
        </p>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="font-semibold text-gray-900 mb-2">Lasīšanas progress</h3>
        <p class="text-gray-600 text-sm">
          Sekojiet, ko plānojat lasīt, ko pašlaik lasāt un ko esat pabeidzis.
        </p>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="font-semibold text-gray-900 mb-2">Diskusijas</h3>
        <p class="text-gray-600 text-sm">
          Izveidojiet diskusijas par grāmatām, dalieties viedokļos un komentējiet.
        </p>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="font-semibold text-gray-900 mb-2">Sociālās funkcijas</h3>
        <p class="text-gray-600 text-sm">
          Sekojiet citiem lasītājiem un skatiet viņu aktivitātes.
        </p>
      </div>
    </div>

    <!-- Call to Action -->
    <div class="text-center bg-blue-50 p-8 rounded-lg">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">
        Gatavi sākt savu lasīšanas ceļojumu?
      </h2>
      <div class="space-x-4">
        <router-link 
          to="/books" 
          class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors"
        >
          Pārlūkot grāmatas
        </router-link>
        <router-link 
          to="/discussions" 
          class="border border-blue-600 text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors"
        >
          Pievienoties diskusijām
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// Home page logic
</script>