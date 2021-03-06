<?php
/**
 * Contains the AppShellViewAware trait.
 *
 * @copyright   Copyright (c) 2018 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2018-09-23
 *
 */

namespace Konekt\AppShell\Http\Controllers;

use Illuminate\Support\Str;

trait AppShellViewAware
{
    /** @var  string The namespace of the views */
    protected $viewNS;

    /**
     * A tiny wrapper for view() method that handles cases when the
     * 'appshell::' view namespace gets customized, or the cases
     * when no namespace gets specified by adding a namespace
     *
     * @param  string  $view
     * @param  array   $data
     * @param  array   $mergeData
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    protected function appShellView($view = null, $data = [], $mergeData = [])
    {
        if (null === $this->viewNS) {
            $this->viewNS = config('konekt.app_shell.views.namespace');
        }

        if (!Str::contains($view, '::')) {
            $view = sprintf('%s::%s', $this->viewNS, $view);
        } elseif (Str::startsWith($view, 'appshell::')) {
            $view = Str::replaceFirst('appshell::', $this->viewNS . '::', $view);
        }

        return view($view, $data, $mergeData);
    }
}
