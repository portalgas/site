	<div class="alert alert-danger">


        <table cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    Vuoi forzare la chiusura dell'ordine nonostante alcuni gasisti non hanno ancora saldato?
                    <br />
                    Una volta chiuso l'ordine
                    <ul>
                        <li>non sarà più possibile saldate gli importi mancanti</li>
                        <li>l'ordine andrà in statistiche</li>
                    </ul>
                </td>
                <td style="vertical-align: middle">
                    <?php
                    echo $this->Html->link(__('Force Close Order'), ['controller' => 'Orders', 'action' => 'close', $results['Order']['id']], ['class' => 'btn btn-primary', 'title' => __('Force Close Order')]);
                    ?>
                </td>
            </tr>
        </table>


	</div>
