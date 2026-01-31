    </main>

    <footer class="bg-gray-800 text-white py-6 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; Design by Muhamad Rizqi Duha Pramudya</p>
        </div>
    </footer>
    
    <script>
        // Simple dropdown functionality using hover
        function showDropdown(element) {
            const dropdown = element.querySelector('.dropdown-menu-content');
            if (dropdown) {
                dropdown.classList.remove('hidden');
            }
        }
        
        function hideDropdown(element) {
            const dropdown = element.querySelector('.dropdown-menu-content');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
