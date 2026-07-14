<form method="POST" action="{{ route('prudential.initiate') }}">
    @csrf
    <input type="hidden" name="amount" value="{{ $amount ?? 10.00 }}">
    <button type="submit" class="btn btn-primary w-100">
        Pay with Prudential Bank
    </button>
</form>
