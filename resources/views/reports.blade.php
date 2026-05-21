@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
	<div class="row mb-4">
		<div class="col-12">
			<div class="card">
				<div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
					<div>
						<h4 class="mb-1">Monthly Reports</h4>
						<p class="mb-0 text-muted">Generate Hitad and Lahipita payment reports by month.</p>
					</div>

					<div class="d-flex flex-column gap-2 align-items-md-end">
						<form action="{{ url('/reports') }}" method="GET" class="d-flex gap-2 align-items-center">
							<input type="month" name="month" class="form-control" value="{{ $monthInput }}">
							<button type="submit" class="btn btn-primary">
								Generate
							</button>
						</form>

						<div class="d-flex flex-wrap gap-2 justify-content-md-end">
							<a href="#hitad-paid" class="btn btn-sm btn-outline-success">Hitad Paid</a>
							<a href="#hitad-unpaid" class="btn btn-sm btn-outline-warning">Hitad Unpaid</a>
							<a href="#lahipita-paid" class="btn btn-sm btn-outline-success">Lahipita Paid</a>
							<a href="#lahipita-unpaid" class="btn btn-sm btn-outline-warning">Lahipita Unpaid</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mb-4 g-3">
		<div class="col-md-3 col-sm-6">
			<div class="card text-center">
				<div class="card-body">
					<div class="text-muted mb-1">Hitad Paid</div>
					<h3 class="mb-0 text-success">{{ $reportSections['hitad_paid']['count'] }}</h3>
				</div>
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="card text-center">
				<div class="card-body">
					<div class="text-muted mb-1">Hitad Unpaid</div>
					<h3 class="mb-0 text-warning">{{ $reportSections['hitad_unpaid']['count'] }}</h3>
				</div>
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="card text-center">
				<div class="card-body">
					<div class="text-muted mb-1">Lahipita Paid</div>
					<h3 class="mb-0 text-success">{{ $reportSections['lahipita_paid']['count'] }}</h3>
				</div>
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="card text-center">
				<div class="card-body">
					<div class="text-muted mb-1">Lahipita Unpaid</div>
					<h3 class="mb-0 text-warning">{{ $reportSections['lahipita_unpaid']['count'] }}</h3>
				</div>
			</div>
		</div>
	</div>

	@php
		$sections = [
			'hitad_paid' => ['title' => 'Hitad Paid', 'badge' => 'success', 'empty' => 'No Hitad paid ads found for this month.'],
			'hitad_unpaid' => ['title' => 'Hitad Unpaid', 'badge' => 'warning', 'empty' => 'No Hitad unpaid ads found for this month.'],
			'lahipita_paid' => ['title' => 'Lahipita Paid', 'badge' => 'success', 'empty' => 'No Lahipita paid ads found for this month.'],
			'lahipita_unpaid' => ['title' => 'Lahipita Unpaid', 'badge' => 'warning', 'empty' => 'No Lahipita unpaid ads found for this month.'],
		];
	@endphp

	<div class="row g-4">
		@foreach($sections as $key => $section)
			@php($report = $reportSections[$key])
			<div class="col-12" id="{{ str_replace('_', '-', $key) }}">
				<div class="card">
					<div class="card-header d-flex justify-content-between align-items-center">
						<div>
							<h5 class="mb-0">{{ $section['title'] }}</h5>
							<small class="text-muted">{{ $monthLabel }}</small>
						</div>
						<div class="d-flex align-items-center gap-2">
							<span class="badge bg-{{ $section['badge'] }}">{{ $report['count'] }} records</span>
							<a href="{{ url('/reports/' . $key . '/pdf') . '?month=' . $monthInput }}" download class="btn btn-sm btn-outline-primary">
								PDF Export
							</a>
						</div>
					</div>

					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table table-hover mb-0 align-middle">
								<thead class="table-light">
									<tr>
										<th>ID</th>
										<th>Customer</th>
										<th>Category</th>
										<th>District</th>
										<th>City</th>
										<th>Publish Date</th>
										<th>Amount</th>
										<th>Payment Status</th>
										<th>Payment Date</th>
									</tr>
								</thead>
								<tbody>
									@forelse($report['ads'] as $ad)
										<tr>
											<td>{{ $ad->id }}</td>
											<td>{{ $ad->customer_name }}</td>
											<td>{{ $ad->category_name }}</td>
											<td>{{ $ad->district_name }}</td>
											<td>{{ $ad->city_name }}</td>
											<td>{{ $ad->publish_date ? \Illuminate\Support\Carbon::parse($ad->publish_date)->format('Y-m-d') : '-' }}</td>
											<td>{{ is_null($ad->amount) ? '-' : 'Rs. ' . number_format($ad->amount, 2) }}</td>
											<td>@include('partials.payment-status-badge', ['status' => $ad->payment_status])</td>
											<td>{{ $ad->payment_date ? \Illuminate\Support\Carbon::parse($ad->payment_date)->format('Y-m-d') : '-' }}</td>
										</tr>
									@empty
										<tr>
											<td colspan="9" class="text-center text-muted py-4">
												{{ $section['empty'] }}
											</td>
										</tr>
									@endforelse
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
</div>
@endsection
