<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\BaseController;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DatabaseSettingController extends BaseController
{

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $filterTables = array('addon_settings','admin_roles','admins','business_settings','colors','currencies',
                                'failed_jobs','migrations','oauth_access_tokens','oauth_auth_codes',
                                'oauth_clients','oauth_personal_access_clients','oauth_refresh_tokens',
                                'password_resets','personal_access_tokens','phone_or_email_verifications',
                                'social_medias','soft_credentials','users');
        $tables = array_values(array_diff($tables, $filterTables));

        $rows = [];
        foreach ($tables as $table) {
            $count = DB::table($table)->count();
            $rows[] = $count;
        }

        return view('admin-views.business-settings.db-index', compact('tables', 'rows'));
    }

    public function delete(Request $request): RedirectResponse
    {
        $tables = (array)$request['tables'];

        // Server-side whitelist: only tables the UI itself lists may be cleaned.
        // Rebuilt here from the same exclusion list as getView() — never trust
        // the table names coming from the request.
        $protectedTables = array('addon_settings','admin_roles','admins','business_settings','colors','currencies',
                                'failed_jobs','migrations','oauth_access_tokens','oauth_auth_codes',
                                'oauth_clients','oauth_personal_access_clients','oauth_refresh_tokens',
                                'password_resets','personal_access_tokens','phone_or_email_verifications',
                                'social_medias','soft_credentials','users');
        $allowedTables = array_values(array_diff(DB::connection()->getDoctrineSchemaManager()->listTableNames(), $protectedTables));
        $tables = array_values(array_intersect($tables, $allowedTables));

        if(count($tables) == 0) {
            ToastMagic::error(translate('No_Table_Updated'));
            return back();
        }

        try {
            DB::transaction(function () use ($tables) {
                foreach ($tables as $table) {
                    DB::table($table)->delete();
                }
            });
        } catch (Exception $exception) {
            ToastMagic::error(translate('Failed_to_update'));
            return back();
        }

        ToastMagic::success(translate('Updated_successfully'));
        return back();
    }
}
