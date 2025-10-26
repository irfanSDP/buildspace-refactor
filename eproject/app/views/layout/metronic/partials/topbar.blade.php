<div class="topbar">
	<!--begin::Quick Actions-->
	<div class="dropdown">
		@if(! Confide::user())
			<div class="topbar-item">
				<a href="{{ route('users.login') }}" class="btn btn-text-white bg-white-o-30 d-flex align-items-center px-md-2 w-md-auto">
					{{ trans('projectOpenTenderBM.navLogin') }}
				</a>
			</div>
		@else
			<!--begin::Toggle-->
			<div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px" aria-expanded="false">
				<div class="btn btn-transparent-white d-flex align-items-center px-md-2 w-md-auto">
					<i class="far fa-user-circle"></i>
					<span class="opacity-90 d-none d-md-inline">{{ trans('projectOpenTenderBM.navMenu') }}</span>
				</div>
			</div>
			<!--end::Toggle-->
			<!--begin::Dropdown-->
			<div class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-xl dropdown-menu-anim-up" x-placement="top-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-342px, 5px, 0px);">
				<form>
					<!--begin::Header-->
					<div class="d-flex align-items-center py-10 px-8 bgi-size-cover bgi-no-repeat rounded-top" style="background-image: url({{ asset('metronic/media/misc/bg-1.jpg') }})">
						<div class="symbol symbol-100 svg-light-primary mr-5">
							<div class="symbol-label">
								<span class="svg-icon svg-icon-lg svg-icon-primary svg-icon-7x">
									<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
										<g fill="none" fill-rule="evenodd">
											<polygon points="0 0 24 0 24 24 0 24"></polygon>
											<path d="M12,11 C9.790861,11 8,9.209139 8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 C16,9.209139 14.209139,11 12,11 Z" fill="#000000" opacity="0.3"></path>
											<path d="M3.00065168,20.1992055 C3.38825852,15.4265159 7.26191235,13 11.9833413,13 C16.7712164,13 20.7048837,15.2931929 20.9979143,20.2 C21.0095879,20.3954741 20.9979143,21 20.2466999,21 C16.541124,21 11.0347247,21 3.72750223,21 C3.47671215,21 2.97953825,20.45918 3.00065168,20.1992055 Z" fill="#000000"></path>
										</g>
									</svg>
								</span>
							</div>
						</div>
						<div class="d-flex flex-column">
							<div class="font-weight-bold font-size-h5 text-white">
								{{ Confide::user()->name }}
							</div>
							@if(Confide::user()->company)
								<div class="text-white mt-1">
									{{ Confide::user()->company->name }}
								</div>
							@endif
							<div class="text-white mt-1">
								{{ Confide::user()->email }}
							</div>
						</div>
					</div>
					<!--end::Header-->
					<!--begin::Nav-->
					<ul class="navi navi-hover py-4">
						<!--begin::Item-->
						<li class="navi-item">
							<a href="{{ route('user.updateMyProfile') }}" class="navi-link">
								<span class="symbol symbol-40 symbol-light-primary mr-5">
									<span class="symbol-label">
										<span class="svg-icon svg-icon-lg svg-icon-primary">
											<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
												<g fill="none" fill-rule="evenodd">
													<polygon points="0 0 24 0 24 24 0 24"></polygon>
													<path d="M12,11 C9.790861,11 8,9.209139 8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 C16,9.209139 14.209139,11 12,11 Z" fill="#000000" opacity="0.3"></path>
													<path d="M3.00065168,20.1992055 C3.38825852,15.4265159 7.26191235,13 11.9833413,13 C16.7712164,13 20.7048837,15.2931929 20.9979143,20.2 C21.0095879,20.3954741 20.9979143,21 20.2466999,21 C16.541124,21 11.0347247,21 3.72750223,21 C3.47671215,21 2.97953825,20.45918 3.00065168,20.1992055 Z" fill="#000000"></path>
												</g>
											</svg>
										</span>
									</span>
								</span>
								<span class="navi-text">{{ trans('projectOpenTenderBM.navProfile') }}</span>
							</a>
						</li>
						<!--end::Item-->
						<!--begin::Item-->
						<li class="navi-item">
							<a href="{{ route('home.index') }}" class="navi-link">
								<span class="symbol symbol-40 symbol-light-success mr-5">
									<span class="symbol-label">
										<span class="svg-icon svg-icon-lg svg-icon-success">
											<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
												<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
													<polygon points="0 0 24 0 24 24 0 24"></polygon>
													<path d="M12.2928955,6.70710318 C11.9023712,6.31657888 11.9023712,5.68341391 12.2928955,5.29288961 C12.6834198,4.90236532 13.3165848,4.90236532 13.7071091,5.29288961 L19.7071091,11.2928896 C20.085688,11.6714686 20.0989336,12.281055 19.7371564,12.675721 L14.2371564,18.675721 C13.863964,19.08284 13.2313966,19.1103429 12.8242777,18.7371505 C12.4171587,18.3639581 12.3896557,17.7313908 12.7628481,17.3242718 L17.6158645,12.0300721 L12.2928955,6.70710318 Z" id="Path-94" fill="#000000" fill-rule="nonzero"></path>
													<path d="M3.70710678,15.7071068 C3.31658249,16.0976311 2.68341751,16.0976311 2.29289322,15.7071068 C1.90236893,15.3165825 1.90236893,14.6834175 2.29289322,14.2928932 L8.29289322,8.29289322 C8.67147216,7.91431428 9.28105859,7.90106866 9.67572463,8.26284586 L15.6757246,13.7628459 C16.0828436,14.1360383 16.1103465,14.7686056 15.7371541,15.1757246 C15.3639617,15.5828436 14.7313944,15.6103465 14.3242754,15.2371541 L9.03007575,10.3841378 L3.70710678,15.7071068 Z" id="Path-94" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(9.000003, 11.999999) rotate(-270.000000) translate(-9.000003, -11.999999) "></path>
												</g>
											</svg>
										</span>
									</span>
								</span>
								<span class="navi-text">{{ trans('projectOpenTenderBM.navEproject') }}</span>
							</a>
						</li>
						<!--end::Item-->
						<!--begin::Separator-->
						<div class="separator separator-solid"></div>
						<!--end::Separator-->
						<!--begin::Item-->
						<li class="navi-item">
							<a href="{{ route('users.logout') }}" class="navi-link">
								<span class="symbol symbol-40 symbol-light-danger mr-5">
									<span class="symbol-label">
										<span class="svg-icon svg-icon-lg svg-icon-danger">
											<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
												<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
													<rect x="0" y="0" width="24" height="24"></rect>
													<path d="M14.0069431,7.00607258 C13.4546584,7.00607258 13.0069431,6.55855153 13.0069431,6.00650634 C13.0069431,5.45446114 13.4546584,5.00694009 14.0069431,5.00694009 L15.0069431,5.00694009 C17.2160821,5.00694009 19.0069431,6.7970243 19.0069431,9.00520507 L19.0069431,15.001735 C19.0069431,17.2099158 17.2160821,19 15.0069431,19 L3.00694311,19 C0.797804106,19 -0.993056895,17.2099158 -0.993056895,15.001735 L-0.993056895,8.99826498 C-0.993056895,6.7900842 0.797804106,5 3.00694311,5 L4.00694793,5 C4.55923268,5 5.00694793,5.44752105 5.00694793,5.99956624 C5.00694793,6.55161144 4.55923268,6.99913249 4.00694793,6.99913249 L3.00694311,6.99913249 C1.90237361,6.99913249 1.00694311,7.89417459 1.00694311,8.99826498 L1.00694311,15.001735 C1.00694311,16.1058254 1.90237361,17.0008675 3.00694311,17.0008675 L15.0069431,17.0008675 C16.1115126,17.0008675 17.0069431,16.1058254 17.0069431,15.001735 L17.0069431,9.00520507 C17.0069431,7.90111468 16.1115126,7.00607258 15.0069431,7.00607258 L14.0069431,7.00607258 Z" id="Path-103" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(9.006943, 12.000000) scale(-1, 1) rotate(-90.000000) translate(-9.006943, -12.000000) "></path>
													<rect fill="#000000" opacity="0.3" transform="translate(14.000000, 12.000000) rotate(-270.000000) translate(-14.000000, -12.000000) " x="13" y="6" width="2" height="12" rx="1"></rect>
													<path d="M21.7928932,9.79289322 C22.1834175,9.40236893 22.8165825,9.40236893 23.2071068,9.79289322 C23.5976311,10.1834175 23.5976311,10.8165825 23.2071068,11.2071068 L20.2071068,14.2071068 C19.8165825,14.5976311 19.1834175,14.5976311 18.7928932,14.2071068 L15.7928932,11.2071068 C15.4023689,10.8165825 15.4023689,10.1834175 15.7928932,9.79289322 C16.1834175,9.40236893 16.8165825,9.40236893 17.2071068,9.79289322 L19.5,12.0857864 L21.7928932,9.79289322 Z" id="Path-104" fill="#000000" fill-rule="nonzero" transform="translate(19.500000, 12.000000) rotate(-90.000000) translate(-19.500000, -12.000000) "></path>
												</g>
											</svg>
										</span>
									</span>
								</span>
								<span class="navi-text">{{ trans('projectOpenTenderBM.navLogout') }}</span>
							</a>
						</li>
						<!--end::Item-->
					</ul>
					<!--end::Nav-->
				</form>
			</div>
			<!--end::Dropdown-->
		@endif
	</div>
	<!--end::Quick Actions-->
</div>