<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Filter role-based access control (RBAC) sederhana. */
class RoleFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        if (! auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $session = session();
        $userRole = strtolower($session->get('role') ?? '');

        if ($userRole === '') {
            $user = auth()->user();
            if ($user !== null) {
                $profile = \Config\Database::connect()
                    ->table('user_profile')
                    ->where('user_id', $user->id)
                    ->get()
                    ->getRowArray();

                if ($profile && ! empty($profile['role'])) {
                    $userRole = strtolower($profile['role']);
                    $session->set('role', $userRole);
                }
            }
        }

        if (empty($arguments)) {
            return;
        }

        $allowedRoles = array_map('strtolower', $arguments);
        if ($userRole === '' || ! in_array($userRole, $allowedRoles, true)) {
            return redirect()->to('/unauthorized');
        }
    }


    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
