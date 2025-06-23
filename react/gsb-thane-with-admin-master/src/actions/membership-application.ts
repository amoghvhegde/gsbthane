
'use server';

import { z } from 'zod';

// Define the schema for membership application data, matching the client-side form schema
const MembershipApplicationSchema = z.object({
  firstName: z.string().min(1, "First name is required"),
  middleName: z.string().optional(),
  surname: z.string().min(1, "Surname is required"),
  gender: z.enum(["Male", "Female"]),
  postalAddress: z.string().min(1, "Postal address is required"),
  pinCode: z.string().length(6, "Pin code must be 6 digits").regex(/^\d{6}$/),
  mobileNo: z.string().length(10, "Mobile number must be 10 digits").regex(/^\d{10}$/),
  email: z.string().email(), // Already transformed to uppercase on client
  dateOfBirth: z.string(), // Expecting formatted date string 'dd-MM-yyyy'
  occupation: z.string().min(1, "Occupation is required"),
  qualification: z.string().min(1, "Qualification is required"),
  maritalStatus: z.enum(["Married", "Unmarried"]),
  numChildren: z.number().int().nonnegative().optional(), // Expecting number or undefined
  gotra: z.string().min(1, "Gotra is required"),
  kuladevata: z.string().min(1, "Kuladevata is required"),
  math: z.enum(["Kashi", "Gokarn", "Kavale"]),
  nativePlace: z.string().min(1, "Native place is required"),
  otherGSBInstitutions: z.string().optional(),
  membershipType: z.enum(["Life", "Patron"]),
  introducerName: z.string().optional(),
  declaration: z.boolean().refine(val => val === true),
}).refine(data => data.maritalStatus === "Unmarried" || (data.maritalStatus === "Married" && data.numChildren !== undefined), {
  message: "Number of children is required if married",
  path: ["numChildren"],
});


export type MembershipApplicationData = z.infer<typeof MembershipApplicationSchema>;

interface SubmissionResult {
  success: boolean;
  message?: string;
  data?: MembershipApplicationData;
}

export async function submitMembershipApplication(data: MembershipApplicationData): Promise<SubmissionResult> {
  try {
    // Validate the data against the schema
    const validatedData = MembershipApplicationSchema.parse(data);

    console.log("Membership Application Data Received:", validatedData);

    // TODO: Implement database interaction here
    // Example: await db.collection('membershipApplications').add(validatedData);
    // For now, we'll simulate a successful submission.
    
    // Simulate some processing delay
    await new Promise(resolve => setTimeout(resolve, 1000));

    return { 
      success: true, 
      message: "Membership application submitted successfully.",
      data: validatedData 
    };

  } catch (error) {
    console.error("Error submitting membership application:", error);
    if (error instanceof z.ZodError) {
      return { success: false, message: "Validation failed: " + error.errors.map(e => e.message).join(', ') };
    }
    return { success: false, message: "An unexpected error occurred during submission." };
  }
}
