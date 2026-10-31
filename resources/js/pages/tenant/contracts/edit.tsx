import ContractController from '@/actions/App/Http/Controllers/Tenant/ContractController';
import ContractForm, {
    type ContractFormOptions,
    type ContractFormValues,
} from '@/components/contracts/contract-form';
import FormPage from '@/components/form-page';

export default function ContractsEdit({
    contract,
    ...options
}: ContractFormOptions & { contract: ContractFormValues & { id: number } }) {
    return (
        <FormPage title="Editar contrato">
            <ContractForm
                action={ContractController.update.form({
                    contract: contract.id,
                })}
                {...options}
                defaults={contract}
                submitLabel="Guardar cambios"
            />
        </FormPage>
    );
}
