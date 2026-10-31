import ContractController from '@/actions/App/Http/Controllers/Tenant/ContractController';
import ContractForm, {
    type ContractFormOptions,
} from '@/components/contracts/contract-form';
import FormPage from '@/components/form-page';

export default function ContractsCreate(options: ContractFormOptions) {
    return (
        <FormPage title="Nuevo contrato">
            <ContractForm
                action={ContractController.store.form()}
                {...options}
                submitLabel="Crear contrato"
            />
        </FormPage>
    );
}
