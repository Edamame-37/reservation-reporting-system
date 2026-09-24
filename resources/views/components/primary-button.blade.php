<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex justify-center items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-wider hover:from-indigo-500 hover:to-purple-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transform active:scale-95 transition-all ease-in-out duration-200 shadow-lg hover:shadow-indigo-500/30 w-full']) }}>
    {{ $slot }}
</button>
