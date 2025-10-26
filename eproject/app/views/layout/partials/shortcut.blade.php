<div id="shortcut">
	<ul>
		@if ($user->isSuperAdmin())
			<li>
				<a href="{{ route('companies') }}" class="jarvismetro-tile big-cubes bg-color-blue @if (Request::is('companies*')) {{{'selected'}}} @endif">
					<span class="iconbox"> <i class="fa fa-building fa-4x"></i>
						<span>{{ trans('navigation/mainnav.companies') }}
							<span class="label pull-right bg-color-blue">
								{{{ $companyCount }}}
							</span>
						</span>
					</span>
				</a>
			</li>
			<li>
				<a href="{{ route('contracts') }}" class="jarvismetro-tile big-cubes bg-color-purple @if (Request::is('clauses*')) {{{'selected'}}} @endif">
					<span class="iconbox"> <i class="fa fa-list fa-4x"></i>
						<span>{{ trans('navigation/mainnav.contracts') }}
							&nbsp;
						</span>
					</span>
				</a>
			</li>
			<li>
				<a href="{{ route('countries') }}" class="jarvismetro-tile big-cubes bg-color-blueDark @if (Request::is('countries*')) {{{'selected'}}} @endif">
					<span class="iconbox"> <i class="fa fa-flag fa-4x"></i>
						<span>{{ trans("navigation/mainnav.countries") }}
							&nbsp;
						</span>
					</span>
				</a>
			</li>
			<li>
				<a href="{{ route('calendars') }}" class="jarvismetro-tile big-cubes bg-color-greenLight @if (Request::is('calendars')) {{{'selected'}}} @endif">
					<span class="iconbox"> <i class="fa fa-calendar fa-4x"></i>
						<span>{{ trans("navigation/mainnav.calendars") }}
							&nbsp;
						</span>
					</span>
				</a>
			</li>
			<li>
				<a href="{{ route('myCompanyProfiles.edit') }}" class="jarvismetro-tile big-cubes bg-color-blueDark @if (Request::is('my_company_profiles*')) {{{'selected'}}} @endif">
					<span class="iconbox"> <i class="fa fa-briefcase fa-4x"></i>
						<span>{{ trans("navigation/mainnav.myCompProfile") }}</span>
					</span>
				</a>
			</li>
		@else
			<li>
				<a href="{{ route('companies.profile') }}" class="jarvismetro-tile big-cubes bg-color-blue @if (Request::is('companies*')) {{{'selected'}}} @endif">
					<span class="iconbox"> <i class="fa fa-building fa-4x"></i>
						<span>{{ trans("navigation/mainnav.myCompany") }}</span>
					</span>
				</a>
			</li>
			@if ( $user->isGroupAdmin() )
				<li>
					<a href="{{ route('companies.users', array($user->company_id)) }}" class="jarvismetro-tile big-cubes bg-color-blueDark @if (Request::is('users*')) {{{'selected'}}} @endif">
						<span class="iconbox"> <i class="fa fa-users fa-4x"></i>
							<span>{{ trans("navigation/mainnav.manageUsers") }}</span>
						</span>
					</a>
				</li>
			@endif
		@endif

		<li>
			<a href="{{ route('user.updateMyProfile') }}" class="jarvismetro-tile big-cubes bg-color-magenta @if (Request::is('my_profile')) {{{'selected'}}} @endif">
				<span class="iconbox"> <i class="fa fa-user fa-4x"></i>
					<span>{{ trans("navigation/mainnav.myProfile") }}</span>
				</span>
			</a>
		</li>
		@if($user->isSuperAdmin() && (!$licensingDisabled))
			<li>
				<a href="{{ route('license.index') }}" class="jarvismetro-tile big-cubes bg-color-blue @if (Request::is('license')) {{{'selected'}}} @endif">
				<span class="iconbox"> <i class="fa fa-id-card fa-4x"></i>
					<span>{{ trans("navigation/mainnav.licensing") }}</span>
				</span>
				</a>
			</li>
		@endif
		<li>
			<a href="{{ route('user.settings.edit') }}" class="jarvismetro-tile big-cubes bg-color-greenDark @if (Request::is('settings')) {{{'selected'}}} @endif">
			<span class="iconbox"> <i class="fa fa-cogs fa-4x"></i>
				<span>{{ trans("navigation/mainnav.settings") }}</span>
			</span>
			</a>
		</li>
	</ul>
</div>