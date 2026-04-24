<div class="bg-gray-800 text-white py-8 px-4">
    <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
        <div class="mb-4 md:mb-0">
            <h3 class="text-2xl font-bold">S'inscrire à notre newsletter</h3>
            <p class="text-gray-400">Restez informé de nos dernières offres et produits.</p>
        </div>
        <form id="newsletter-form" action="{{ route('newsletter.subscribe') }}" method="POST" class="flex">
            @csrf
            <input type="email" name="email" placeholder="Votre adresse email" class="bg-gray-700 text-white rounded-l-lg py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-r-lg py-2 px-4">S'inscrire</button>
        </form>
    </div>
    <div id="newsletter-message" class="mt-4 text-center"></div>
</div>

@push('scripts')
<script>
    document.getElementById('newsletter-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = e.target;
        const email = form.querySelector('input[name="email"]').value;
        const messageDiv = document.getElementById('newsletter-message');
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.errors) {
                messageDiv.innerHTML = `<p class="text-red-500">${data.errors.email[0]}</p>`;
            } else {
                messageDiv.innerHTML = `<p class="text-green-500">${data.message}</p>`;
                form.reset();
            }
        })
        .catch(error => {
            messageDiv.innerHTML = `<p class="text-red-500">Une erreur est survenue.</p>`;
            console.error('Error:', error);
        });
    });
</script>
@endpush